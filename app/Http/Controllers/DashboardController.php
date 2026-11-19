<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectRisk;
use App\Models\ProjectMeeting;
use App\Models\ProjectAuditLog;
use App\Models\UserCalendarNote;
use App\Models\InAppNotification;
use App\Services\ResourceService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // TARİH VE DÖNEM FİLTRESİ PARAMETRELERİ
        $timeFilter = $request->input('time_filter', 'all');
        $dateType = $request->input('date_type', 'created_at');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $startDate = null;
        $endDate = null;
        $activeDateFilterLabel = null;

        if ($timeFilter === 'this_month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
            $activeDateFilterLabel = 'Bu Ay (' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y') . ')';
        } elseif ($timeFilter === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
            $activeDateFilterLabel = 'Geçen Ay (' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y') . ')';
        } elseif ($timeFilter === 'this_quarter') {
            $startDate = now()->startOfQuarter();
            $endDate = now()->endOfQuarter();
            $activeDateFilterLabel = 'Bu Çeyrek (' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y') . ')';
        } elseif ($timeFilter === 'this_year') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
            $activeDateFilterLabel = 'Bu Yıl (' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y') . ')';
        } elseif ($timeFilter === 'last_year') {
            $startDate = now()->subYear()->startOfYear();
            $endDate = now()->subYear()->endOfYear();
            $activeDateFilterLabel = 'Geçen Yıl (' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y') . ')';
        } elseif ($timeFilter === 'custom' || (!empty($dateFrom) || !empty($dateTo))) {
            $timeFilter = 'custom';
            $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
            if ($startDate && $endDate) {
                $activeDateFilterLabel = 'Özel Aralık (' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y') . ')';
            } elseif ($startDate) {
                $activeDateFilterLabel = $startDate->format('d.m.Y') . ' Tarihinden İtibaren';
            } elseif ($endDate) {
                $activeDateFilterLabel = $endDate->format('d.m.Y') . ' Tarihine Kadar';
            }
        }

        // Form alanlarının seçilen tarihlerle doldurulması
        if ($startDate && empty($dateFrom)) {
            $dateFrom = $startDate->format('Y-m-d');
        }
        if ($endDate && empty($dateTo)) {
            $dateTo = $endDate->format('Y-m-d');
        }

        // 1. TEMEL PROJE KAPSAMI (Bölüm & Kullanıcı İzolasyonlu + Tarih Filtreli)
        $scopedProjects = Project::where('is_template', false)->forUser($user);

        if ($startDate && $endDate) {
            if ($dateType === 'start_date') {
                $scopedProjects->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            } elseif ($dateType === 'target_end_date') {
                $scopedProjects->whereBetween('target_end_date', [$startDate->toDateString(), $endDate->toDateString()]);
            } else {
                $scopedProjects->whereBetween('created_at', [$startDate, $endDate]);
            }
        } elseif ($startDate) {
            if ($dateType === 'start_date') {
                $scopedProjects->where('start_date', '>=', $startDate->toDateString());
            } elseif ($dateType === 'target_end_date') {
                $scopedProjects->where('target_end_date', '>=', $startDate->toDateString());
            } else {
                $scopedProjects->where('created_at', '>=', $startDate);
            }
        } elseif ($endDate) {
            if ($dateType === 'start_date') {
                $scopedProjects->where('start_date', '<=', $endDate->toDateString());
            } elseif ($dateType === 'target_end_date') {
                $scopedProjects->where('target_end_date', '<=', $endDate->toDateString());
            } else {
                $scopedProjects->where('created_at', '<=', $endDate);
            }
        }

        // A. Kullanıcının Üzerindeki Görevler (Acil & Açık Görevler - Tarih Kapsamında)
        $myTasksQuery = ProjectTask::with(['project', 'assignee'])
            ->whereIn('project_id', (clone $scopedProjects)->select('id'))
            ->where('status', '!=', 'done');

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $myTasksQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere(function ($sq) use ($user) {
                      $sq->whereNull('assigned_to')
                         ->whereHas('project.stakeholders', function ($ssq) use ($user) {
                             $ssq->where('user_id', $user->id);
                         });
                  });
            });
        }
        $myTasks = $myTasksQuery->orderBy('due_date', 'asc')->take(6)->get();
        $myOpenTasksCount = $myTasksQuery->count();

        // B. Kullanıcının Dahil Olduğu Projeler (Tarih Kapsamında)
        $myProjectsQuery = (clone $scopedProjects)->with(['bolum', 'manager', 'phases'])
            ->where('status', '!=', 'completed');

        $myProjects = (clone $myProjectsQuery)->orderBy('updated_at', 'desc')->take(5)->get();
        $myProjectsCount = $myProjectsQuery->count();

        // C. Kullanıcının Yaklaşan Toplantıları & Ajandası
        $myMeetingsQuery = ProjectMeeting::with(['project', 'organizer'])
            ->whereIn('project_id', (clone $scopedProjects)->select('id'))
            ->where('meeting_date', '>=', now()->startOfDay())
            ->where('status', '!=', 'completed');

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $myMeetingsQuery->where(function ($q) use ($user) {
                $q->where('organizer_id', $user->id)
                  ->orWhereHas('participants', function ($sq) use ($user) {
                      $sq->where('user_id', $user->id);
                  })
                  ->orWhereHas('project.stakeholders', function ($sq) use ($user) {
                      $sq->where('user_id', $user->id);
                  });
            });
        }
        $myMeetings = $myMeetingsQuery->orderBy('meeting_date', 'asc')->take(4)->get();

        // D. Şirket & Saha Duyuruları (Son kritik onaylar, İSG kilitleri ve yeni projeler)
        $announcementsQuery = ProjectAuditLog::with(['project', 'user'])
            ->whereIn('project_id', (clone $scopedProjects)->select('id'))
            ->whereIn('action', ['approved', 'gatekeeper_approval', 'created', 'reverted']);

        if ($startDate && $endDate) {
            $announcementsQuery->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $announcementsQuery->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $announcementsQuery->where('created_at', '<=', $endDate);
        }

        $announcements = $announcementsQuery->latest()->take(5)->get();

        // E. Kişisel Takvim Notları
        $calendarNotes = UserCalendarNote::where('user_id', $user->id)
            ->orderBy('note_date', 'asc')
            ->get();

        // 2. FABRİKA / BÖLÜM YÖNETİM & KPI ANALİTİĞİ (Tarih ve Yetki Kapsamlı)
        $totalProjects = (clone $scopedProjects)->count();
        $inProgressProjects = (clone $scopedProjects)->where('status', 'in_progress')->count();
        $waitingApprovalProjects = (clone $scopedProjects)->where('status', 'waiting_approval')->count();
        $completedProjects = (clone $scopedProjects)->where('status', 'completed')->count();

        // Finansal Özet
        $totalPlannedBudget = (clone $scopedProjects)->sum('planned_budget');
        $totalActualSpent = (clone $scopedProjects)->sum('actual_spent');

        // Bekleyen Onaylar ("Kimde Bekliyor?" Darboğaz Radarı)
        $waitingProjects = (clone $scopedProjects)->with(['waitingRole', 'waitingUser', 'bolum'])
            ->whereNotNull('current_waiting_role_id')
            ->orderBy('waiting_since', 'asc')
            ->take(5)
            ->get();

        // Geciken Görevler
        $overdueTasks = ProjectTask::with(['project', 'assignee'])
            ->whereIn('project_id', (clone $scopedProjects)->select('id'))
            ->where('status', '!=', 'done')
            ->where('due_date', '<', now())
            ->take(5)
            ->get();

        // Açık Kritik Riskler
        $criticalRisks = ProjectRisk::with(['project', 'owner'])
            ->whereIn('project_id', (clone $scopedProjects)->select('id'))
            ->whereIn('severity', ['high', 'critical'])
            ->where('status', 'open')
            ->take(5)
            ->get();

        // Kaynak Çakışma Analizi
        $capacityAnalysis = app(ResourceService::class)->getCapacityAnalysis($user);
        $hasResourceConflicts = collect($capacityAnalysis['user_rows'] ?? [])->where('has_conflict', true)->count();

        // 3. ÇİFT METRİKLİ YÖNETİCİ KPI'LARI & GÖREV ANALİTİĞİ (CELOXIS / PPM MODELİ)
        $taskBaseQuery = ProjectTask::whereIn('project_id', (clone $scopedProjects)->select('id'));
        $totalTasksCount = (clone $taskBaseQuery)->count();
        $todoTasksCount = (clone $taskBaseQuery)->where('status', 'todo')->count();
        $inProgressTasksCount = (clone $taskBaseQuery)->where('status', 'in_progress')->count();
        $reviewTasksCount = (clone $taskBaseQuery)->where('status', 'review')->count();
        $doneTasksCount = (clone $taskBaseQuery)->where('status', 'done')->count();
        $taskCompletionRate = $totalTasksCount > 0 ? round(($doneTasksCount / $totalTasksCount) * 100) : 0;

        // Efor / Saat İlerlemesi
        $totalEstimatedHours = (float)ProjectTask::whereIn('project_id', (clone $scopedProjects)->select('id'))->sum('estimated_hours');
        $totalSpentHours = (float)ProjectTask::whereIn('project_id', (clone $scopedProjects)->select('id'))->sum('spent_hours');
        $effortCompletionRate = $totalEstimatedHours > 0 ? round(($totalSpentHours / $totalEstimatedHours) * 100, 1) : 0;

        // Bütçe Oranı ve Kalan
        $budgetUsageRate = $totalPlannedBudget > 0 ? round(($totalActualSpent / $totalPlannedBudget) * 100, 1) : 0;
        $remainingBudget = max(0, $totalPlannedBudget - $totalActualSpent);

        // Portföy Projeleri ve Sağlık Dağılımı (Takvim & Bütçe Durumu)
        $portfolioProjects = (clone $scopedProjects)->with(['bolum', 'manager', 'phases'])
            ->orderBy('updated_at', 'desc')
            ->take(6)
            ->get();

        $allProjects = (clone $scopedProjects)->get();
        $onTrackProjectsCount = $allProjects->filter(fn($p) => $p->schedule_health['status'] === 'on_track' || $p->schedule_health['status'] === 'completed')->count();
        $atRiskProjectsCount = $allProjects->filter(fn($p) => $p->schedule_health['status'] === 'at_risk')->count();
        $overdueProjectsCount = $allProjects->filter(fn($p) => $p->schedule_health['status'] === 'overdue')->count();

        $activeReceivedDelegations = $user->getActiveReceivedDelegations();

        return view('dashboard.index', compact(
            'user',
            'activeReceivedDelegations',
            'myTasks', 'myOpenTasksCount',
            'myProjects', 'myProjectsCount',
            'myMeetings',
            'announcements',
            'calendarNotes',
            'totalProjects', 'inProgressProjects', 'waitingApprovalProjects', 'completedProjects',
            'totalPlannedBudget', 'totalActualSpent', 'waitingProjects', 'overdueTasks',
            'criticalRisks', 'hasResourceConflicts', 'capacityAnalysis',
            'totalTasksCount', 'todoTasksCount', 'inProgressTasksCount', 'reviewTasksCount', 'doneTasksCount', 'taskCompletionRate',
            'totalEstimatedHours', 'totalSpentHours', 'effortCompletionRate', 'budgetUsageRate', 'remainingBudget',
            'portfolioProjects', 'onTrackProjectsCount', 'atRiskProjectsCount', 'overdueProjectsCount',
            'timeFilter', 'dateType', 'dateFrom', 'dateTo', 'activeDateFilterLabel'
        ));
    }

    /**
     * Kullanıcının kişisel takvim verilerini (Görevler, Toplantılar, Notlar) JSON olarak döndürür.
     */
    public function getCalendarEvents(Request $request)
    {
        $user = auth()->user();
        
        $startStr = $request->query('start');
        $endStr = $request->query('end');

        $start = $startStr ? Carbon::parse($startStr)->startOfDay() : now()->startOfMonth()->subDays(7);
        $end = $endStr ? Carbon::parse($endStr)->endOfDay() : now()->endOfMonth()->addDays(7);

        $events = [];

        // 1. Kullanıcının Üzerindeki Görevler (Termin Tarihine Göre)
        $tasksQuery = ProjectTask::with('project')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()]);

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $tasksQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere(function ($sq) use ($user) {
                      $sq->whereNull('assigned_to')
                         ->whereHas('project.stakeholders', function ($ssq) use ($user) {
                             $ssq->where('user_id', $user->id);
                         });
                  });
            });
        }

        foreach ($tasksQuery->get() as $task) {
            $isOverdue = $task->due_date->isPast() && $task->status !== 'done';
            $events[] = [
                'id' => 'task_' . $task->id,
                'raw_id' => $task->id,
                'type' => 'task',
                'title' => $task->title,
                'date' => $task->due_date->format('Y-m-d'),
                'time' => $task->due_date->format('H:i') !== '00:00' ? $task->due_date->format('H:i') : 'Termin',
                'description' => $task->description ?: 'Görev açıklaması bulunmuyor.',
                'project_name' => $task->project?->title ?? '-',
                'project_code' => $task->project?->code ?? '-',
                'status' => $task->status,
                'priority' => $task->priority,
                'is_overdue' => $isOverdue,
                'color' => $isOverdue ? '#ef4444' : '#0284c7', // Red if overdue, else Sky Blue
                'badge_bg' => $isOverdue ? '#fef2f2' : '#e0f2fe',
                'badge_color' => $isOverdue ? '#b91c1c' : '#0369a1',
                'url' => $task->project_id ? route('projects.show', $task->project_id) : '#',
            ];
        }

        // 2. Kullanıcının Toplantıları
        $meetingsQuery = ProjectMeeting::with(['project', 'organizer'])
            ->whereNotNull('meeting_date')
            ->whereBetween('meeting_date', [$start, $end]);

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $meetingsQuery->where(function ($q) use ($user) {
                $q->where('organizer_id', $user->id)
                  ->orWhereHas('participants', function ($sq) use ($user) {
                      $sq->where('user_id', $user->id);
                  })
                  ->orWhereHas('project.stakeholders', function ($sq) use ($user) {
                      $sq->where('user_id', $user->id);
                  });
            });
        }

        foreach ($meetingsQuery->get() as $meeting) {
            $events[] = [
                'id' => 'meeting_' . $meeting->id,
                'raw_id' => $meeting->id,
                'type' => 'meeting',
                'title' => $meeting->title,
                'date' => $meeting->meeting_date->format('Y-m-d'),
                'time' => $meeting->meeting_date->format('H:i'),
                'location' => $meeting->location ?: 'Belirtilmedi',
                'description' => $meeting->agenda ?: 'Gündem maddesi eklenmedi.',
                'project_name' => $meeting->project?->title ?? '-',
                'project_code' => $meeting->project?->code ?? '-',
                'status' => $meeting->status,
                'color' => '#10b981', // Emerald Green
                'badge_bg' => '#d1fae5',
                'badge_color' => '#065f46',
                'url' => route('meetings.show', $meeting->id),
            ];
        }

        // 3. Kullanıcının Kişisel Notları & Hatırlatıcıları
        $notes = UserCalendarNote::where('user_id', $user->id)
            ->whereBetween('note_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        foreach ($notes as $note) {
            $events[] = [
                'id' => 'note_' . $note->id,
                'raw_id' => $note->id,
                'type' => 'note',
                'title' => $note->title,
                'date' => $note->note_date->format('Y-m-d'),
                'time' => $note->note_time ?: 'Tüm Gün',
                'description' => $note->description ?: '',
                'color' => $note->color ?: '#f59e0b', // Amber / Gold
                'badge_bg' => '#fef3c7',
                'badge_color' => '#92400e',
                'send_bell' => $note->send_bell,
                'send_email' => $note->send_email,
                'email_subject' => $note->email_subject,
                'email_body' => $note->email_body,
                'is_sent' => $note->is_sent,
            ];
        }

        return response()->json($events);
    }

    /**
     * Yeni kişisel takvim notu ve hatırlatıcı kaydeder.
     */
    public function storeCalendarNote(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'note_date' => 'required|date',
            'note_time' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:30',
            'send_bell' => 'nullable|boolean',
            'send_email' => 'nullable|boolean',
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string',
        ]);

        $sendBell = $request->boolean('send_bell', true);
        $sendEmail = $request->boolean('send_email', false);

        // Standart şablon doldurma (Kullanıcı e-posta seçtiğinde varsayılan kurumsal metin)
        $defaultSubject = "[Köksan PMS Hatırlatıcı] " . $validated['title'];
        $formattedDate = Carbon::parse($validated['note_date'])->format('d.m.Y');
        $timeStr = !empty($validated['note_time']) ? $validated['note_time'] : 'Tüm Gün';
        $descStr = !empty($validated['description']) ? $validated['description'] : 'Açıklama belirtilmedi.';

        $defaultBody = "Sayın {$user->name},\n\nKöksan Proje Yönetim Sistemi (PPM) kişisel takviminizde planladığınız '{$validated['title']}' konulu hatırlatmanız aşağıdadır:\n\n📅 Tarih: {$formattedDate}\n⏰ Saat: {$timeStr}\n📝 Detay: {$descStr}\n\nİyi çalışmalar dileriz.\nKöksan Enterprise PPM";

        $note = UserCalendarNote::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'note_date' => $validated['note_date'],
            'note_time' => $validated['note_time'] ?? null,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#f59e0b',
            'send_bell' => $sendBell,
            'send_email' => $sendEmail,
            'email_subject' => $sendEmail ? (!empty($validated['email_subject']) ? $validated['email_subject'] : $defaultSubject) : null,
            'email_body' => $sendEmail ? (!empty($validated['email_body']) ? $validated['email_body'] : $defaultBody) : null,
            'is_sent' => false,
        ]);

        // Zil Bildirimi: InAppNotification oluştur
        if ($sendBell) {
            InAppNotification::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'event_key' => 'calendar_reminder',
                'title' => '📌 Takvim Hatırlatması: ' . $note->title,
                'message' => ($note->description ?: "Tarih: {$formattedDate}") . ($note->note_time ? " (Saat: {$note->note_time})" : ''),
                'action_url' => route('dashboard'),
            ]);
        }

        // E-Posta Bildirimi: Mail gönderimi
        if ($sendEmail && $user->email) {
            try {
                $subj = $note->email_subject ?: $defaultSubject;
                $bodyHtml = nl2br(e($note->email_body ?: $defaultBody));
                Mail::html($bodyHtml, function ($msg) use ($user, $subj) {
                    $msg->to($user->email)->subject($subj);
                });
                $note->update(['is_sent' => true]);
            } catch (\Throwable $e) {
                Log::warning("Takvim hatırlatma e-postası iletilemedi: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Takvim notu ve hatırlatıcısı başarıyla kaydedildi.',
            'note' => $note
        ]);
    }

    /**
     * Kişisel takvim notunu siler.
     */
    
    /**
     * Kişisel takvim notunu günceller.
     */
    public function updateCalendarNote(Request $request, $id)
    {
        $user = auth()->user();
        $note = UserCalendarNote::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'note_date' => 'required|date',
            'note_time' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:30',
            'send_bell' => 'nullable|boolean',
            'send_email' => 'nullable|boolean',
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string',
        ]);

        $sendBell = $request->boolean('send_bell', true);
        $sendEmail = $request->boolean('send_email', false);

        $defaultSubject = "[Köksan PMS Hatırlatıcı] " . $validated['title'];
        $formattedDate = Carbon::parse($validated['note_date'])->format('d.m.Y');
        $timeStr = !empty($validated['note_time']) ? $validated['note_time'] : 'Tüm Gün';
        $descStr = !empty($validated['description']) ? $validated['description'] : 'Açıklama belirtilmedi.';
        $defaultBody = "Sayın {$user->name},\n\nKöksan Proje Yönetim Sistemi (PPM) kişisel takviminizde güncellediğiniz '{$validated['title']}' konulu hatırlatmanız aşağıdadır:\n\n📅 Tarih: {$formattedDate}\n⏰ Saat: {$timeStr}\n📝 Detay: {$descStr}\n\nİyi çalışmalar dileriz.\nKöksan Enterprise PPM";

        $note->update([
            'title' => $validated['title'],
            'note_date' => $validated['note_date'],
            'note_time' => $validated['note_time'] ?? null,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#f59e0b',
            'send_bell' => $sendBell,
            'send_email' => $sendEmail,
            'email_subject' => $sendEmail ? (!empty($validated['email_subject']) ? $validated['email_subject'] : $defaultSubject) : null,
            'email_body' => $sendEmail ? (!empty($validated['email_body']) ? $validated['email_body'] : $defaultBody) : null,
        ]);

        // E-Posta Bildirimi: Eğer kullanıcı e-postayı işaretlediyse ve henüz gönderilmediyse
        if ($sendEmail && !$note->is_sent && $user->email) {
            try {
                $subj = $note->email_subject ?: $defaultSubject;
                $bodyHtml = nl2br(e($note->email_body ?: $defaultBody));
                Mail::html($bodyHtml, function ($msg) use ($user, $subj) {
                    $msg->to($user->email)->subject($subj);
                });
                $note->update(['is_sent' => true]);
            } catch (\Throwable $e) {
                Log::warning("Takvim hatırlatma e-postası iletilemedi: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Takvim notu başarıyla güncellendi.',
            'note' => $note
        ]);
    }
    public function destroyCalendarNote($id)
    {
        $note = UserCalendarNote::where('user_id', auth()->id())->findOrFail($id);
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Takvim notu başarıyla silindi.'
        ]);
    }
}

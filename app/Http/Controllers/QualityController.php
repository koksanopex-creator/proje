<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectQuality;
use App\Models\ProjectNonConformance;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QualityController extends Controller
{
    /**
     * Kalite & Kabul Yönetimi Ana Sayfası / Panosu
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $projects = Project::accessibleBy($user)->where('is_template', false)->orderBy('title')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $selectedProjectId = $request->get('project_id');
        $selectedCheckType = $request->get('check_type');
        $selectedStatus = $request->get('status');
        $selectedSeverity = $request->get('severity');
        $search = $request->get('search');
        $activeTab = $request->get('tab', 'checks'); // 'checks' veya 'ncrs'

        // 1. Kalite Kontrol ve Kabul Kriterleri Sorgusu
        $qualityQuery = ProjectQuality::with(['project', 'phase', 'task', 'checker', 'approver', 'nonConformances']);
        
        if ($selectedProjectId) {
            $qualityQuery->where('project_id', $selectedProjectId);
        } else {
            $qualityQuery->whereIn('project_id', $projects->pluck('id'));
        }

        if ($selectedCheckType) {
            $qualityQuery->where('check_type', $selectedCheckType);
        }

        if ($selectedStatus) {
            $qualityQuery->where('status', $selectedStatus);
        }

        if ($search) {
            $qualityQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('standard_code', 'like', "%{$search}%")
                  ->orWhere('acceptance_criteria', 'like', "%{$search}%")
                  ->orWhere('measured_value', 'like', "%{$search}%");
            });
        }

        $qualities = $qualityQuery->orderByRaw("FIELD(status, 'failed', 'conditional', 'pending', 'passed', 'exempted')")
                                 ->latest()
                                 ->paginate(20, ['*'], 'checks_page');

        // 2. Uygunsuzluk / Hata (NCR / Punch List) Sorgusu
        $ncrQuery = ProjectNonConformance::with(['project', 'quality', 'assignee', 'closer']);

        if ($selectedProjectId) {
            $ncrQuery->where('project_id', $selectedProjectId);
        } else {
            $ncrQuery->whereIn('project_id', $projects->pluck('id'));
        }

        if ($selectedSeverity) {
            $ncrQuery->where('severity', $selectedSeverity);
        }

        if ($request->get('ncr_status')) {
            $ncrQuery->where('status', $request->get('ncr_status'));
        }

        if ($search) {
            $ncrQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ncr_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('corrective_action', 'like', "%{$search}%");
            });
        }

        $nonConformances = $ncrQuery->orderByRaw("FIELD(severity, 'critical', 'major', 'minor')")
                                   ->orderByRaw("FIELD(status, 'open', 'in_progress', 'resolved', 'closed')")
                                   ->latest()
                                   ->paginate(20, ['*'], 'ncrs_page');

        // 3. Genel Kurumsal Kalite Metrikleri (KPI)
        $baseQualityCountQuery = ProjectQuality::whereIn('project_id', $projects->pluck('id'));
        if ($selectedProjectId) {
            $baseQualityCountQuery->where('project_id', $selectedProjectId);
        }

        $totalChecks = (clone $baseQualityCountQuery)->count();
        $passedChecks = (clone $baseQualityCountQuery)->whereIn('status', ['passed', 'conditional'])->count();
        $pendingChecks = (clone $baseQualityCountQuery)->where('status', 'pending')->count();
        $failedChecks = (clone $baseQualityCountQuery)->where('status', 'failed')->count();
        $qualityRate = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 100;

        $baseNcrCountQuery = ProjectNonConformance::whereIn('project_id', $projects->pluck('id'));
        if ($selectedProjectId) {
            $baseNcrCountQuery->where('project_id', $selectedProjectId);
        }

        $openNcrs = (clone $baseNcrCountQuery)->whereIn('status', ['open', 'in_progress'])->count();
        $criticalNcrs = (clone $baseNcrCountQuery)->whereIn('status', ['open', 'in_progress'])->where('severity', 'critical')->count();
        $resolvedNcrs = (clone $baseNcrCountQuery)->where('status', 'closed')->count();

        return view('quality.index', compact(
            'projects',
            'users',
            'qualities',
            'nonConformances',
            'selectedProjectId',
            'selectedCheckType',
            'selectedStatus',
            'selectedSeverity',
            'search',
            'activeTab',
            'totalChecks',
            'passedChecks',
            'pendingChecks',
            'failedChecks',
            'qualityRate',
            'openNcrs',
            'criticalNcrs',
            'resolvedNcrs'
        ));
    }

    /**
     * Yeni Kalite Kontrol Noktası / Kabul Kriteri Kaydetme
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'title' => 'required|string|max:255',
            'check_type' => 'required|string|max:50',
            'standard_code' => 'nullable|string|max:100',
            'acceptance_criteria' => 'nullable|string',
            'status' => 'nullable|string|in:pending,passed,conditional,failed,exempted',
            'checked_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'certificate_file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'pending';

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName());
            $path = $file->storeAs('quality_certificates', $filename, 'public');
            $validated['certificate_file'] = 'storage/' . $path;
        }

        $quality = ProjectQuality::create($validated);

        AuditLogService::log(
            'created',
            "Kalite kriteri / test planı eklendi: **{$quality->title}** (Tür: **{$quality->check_type_label}**)",
            'ProjectQuality',
            $quality->id,
            $quality->project_id
        );

        return back()->with('success', "Kalite kontrol kriteri başarıyla oluşturuldu.");
    }

    /**
     * Kalite Testini Değerlendirme / Sonuçlandırma (Geçti / Kaldı / Şartlı Kabul)
     */
    public function evaluate(Request $request, int $id)
    {
        $quality = ProjectQuality::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:passed,conditional,failed,exempted,pending',
            'measured_value' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'certificate_file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'auto_create_ncr' => 'nullable|boolean',
            'ncr_title' => 'nullable|string|max:255',
            'ncr_severity' => 'nullable|in:critical,major,minor',
            'ncr_assignee' => 'nullable|exists:users,id',
        ]);

        $quality->status = $validated['status'];
        $quality->measured_value = $validated['measured_value'] ?? $quality->measured_value;
        $quality->notes = $validated['notes'] ?? $quality->notes;
        $quality->checked_by = auth()->id();
        $quality->checked_at = now();

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName());
            $path = $file->storeAs('quality_certificates', $filename, 'public');
            $quality->certificate_file = 'storage/' . $path;
        }

        $quality->save();

        // Eğer başarısız (failed) ise ve otomatik uygunsuzluk (NCR) açılması istendiyse
        if ($validated['status'] === 'failed' && !empty($validated['auto_create_ncr'])) {
            $year = date('Y');
            $count = ProjectNonConformance::whereYear('created_at', $year)->count() + 1;
            $ncrCode = sprintf('NCR-%s-%04d', $year, $count);

            ProjectNonConformance::create([
                'project_id' => $quality->project_id,
                'quality_id' => $quality->id,
                'ncr_code' => $ncrCode,
                'title' => $validated['ncr_title'] ?: "{$quality->title} - Kalite Test Başarısızlığı",
                'severity' => $validated['ncr_severity'] ?? 'major',
                'status' => 'open',
                'assigned_to' => $validated['ncr_assignee'] ?? null,
                'description' => "Test Sonucu: Başarısız (Ölçülen: {$quality->measured_value}). Şartname: {$quality->acceptance_criteria}. Değerlendirme Notu: {$quality->notes}",
                'created_by' => auth()->id(),
            ]);

            AuditLogService::log(
                'created',
                "Kalite testi başarısızlığı nedeniyle otomatik Uygunsuzluk / DÖF açıldı: **{$ncrCode}**",
                'ProjectNonConformance',
                $quality->id,
                $quality->project_id
            );
        }

        $statusBadge = $quality->status_badge;
        AuditLogService::log(
            'updated',
            "Kalite testi sonuçlandırıldı: **{$quality->title}** &rarr; **{$statusBadge['label']}** (Ölçülen: **{$quality->measured_value}**)",
            'ProjectQuality',
            $quality->id,
            $quality->project_id
        );

        return back()->with('success', "Kalite kontrol sonucu kaydedildi.");
    }

    /**
     * Kalite Kontrol Kriterini Silme
     */
    public function destroy(int $id)
    {
        $quality = ProjectQuality::findOrFail($id);
        $title = $quality->title;
        $projectId = $quality->project_id;

        $quality->delete();

        AuditLogService::log(
            'deleted',
            "Kalite kontrol kriteri silindi: **{$title}**",
            'ProjectQuality',
            $id,
            $projectId
        );

        return back()->with('success', "Kalite kriteri başarıyla silindi.");
    }

    /**
     * Yeni Kalite Uygunsuzluğu (NCR / Hata / Punch List) Kaydetme
     */
    public function storeNcr(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'quality_id' => 'nullable|exists:project_qualities,id',
            'title' => 'required|string|max:255',
            'severity' => 'required|in:critical,major,minor',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'evidence_file' => 'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $year = date('Y');
        $count = ProjectNonConformance::whereYear('created_at', $year)->count() + 1;
        $ncrCode = sprintf('NCR-%s-%04d', $year, $count);

        $validated['ncr_code'] = $ncrCode;
        $validated['status'] = 'open';
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $filename = time() . '_' . Str::slug($file->getClientOriginalName());
            $path = $file->storeAs('ncr_evidences', $filename, 'public');
            $validated['evidence_file'] = 'storage/' . $path;
        }

        $ncr = ProjectNonConformance::create($validated);

        AuditLogService::log(
            'created',
            "Yeni Kalite Uygunsuzluğu (NCR) açıldı: **{$ncr->ncr_code}** - **{$ncr->title}** (Şiddet: **{$ncr->severity_badge['label']}**)",
            'ProjectNonConformance',
            $ncr->id,
            $ncr->project_id
        );

        return back()->with('success', "Uygunsuzluk (NCR) kaydı açıldı: {$ncr->ncr_code}");
    }

    /**
     * Uygunsuzluk (NCR) Durumunu Güncelleme / Kapatma
     */
    public function updateNcr(Request $request, int $id)
    {
        $ncr = ProjectNonConformance::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $oldStatus = $ncr->status;
        $newStatus = $validated['status'];

        $ncr->status = $newStatus;
        if (!empty($validated['resolution_notes'])) {
            $ncr->resolution_notes = $validated['resolution_notes'];
        }
        if (!empty($validated['corrective_action'])) {
            $ncr->corrective_action = $validated['corrective_action'];
        }
        if (array_key_exists('assigned_to', $validated)) {
            $ncr->assigned_to = $validated['assigned_to'];
        }
        if (array_key_exists('due_date', $validated)) {
            $ncr->due_date = $validated['due_date'];
        }

        if ($newStatus === 'resolved' && $oldStatus !== 'resolved') {
            $ncr->resolved_at = now();
        }

        if ($newStatus === 'closed' && $oldStatus !== 'closed') {
            $ncr->closed_by = auth()->id();
            $ncr->closed_at = now();
        }

        $ncr->save();

        AuditLogService::log(
            'updated',
            "NCR durumu güncellendi: **{$ncr->ncr_code}** &rarr; **{$ncr->status_badge['label']}**",
            'ProjectNonConformance',
            $ncr->id,
            $ncr->project_id
        );

        return back()->with('success', "NCR kaydı güncellendi ({$ncr->ncr_code}).");
    }

    /**
     * Uygunsuzluk (NCR) Silme
     */
    public function destroyNcr(int $id)
    {
        $ncr = ProjectNonConformance::findOrFail($id);
        $code = $ncr->ncr_code;
        $projectId = $ncr->project_id;

        $ncr->delete();

        AuditLogService::log(
            'deleted',
            "NCR kaydı silindi: **{$code}**",
            'ProjectNonConformance',
            $id,
            $projectId
        );

        return back()->with('success', "NCR kaydı silindi: {$code}");
    }
}

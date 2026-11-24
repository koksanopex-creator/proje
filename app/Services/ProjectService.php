<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use App\Models\ProjectStakeholder;
use App\Models\ProjectBudgetItem;
use App\Models\ProjectRisk;
use App\Models\ProjectBom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Şablon projeyi klonlayarak yeni bir proje başlatır.
     */
    public function createFromTemplate(int $templateId, array $projectData): Project
    {
        return DB::transaction(function () use ($templateId, $projectData) {
            $template = Project::with(['phases.tasks', 'risks', 'boms', 'stakeholders'])->findOrFail($templateId);

            $project = Project::create(array_merge($projectData, [
                'is_template' => false,
                'status' => 'draft',
                'progress_percentage' => 0,
            ]));

            // 1. Fazları ve Görevleri Klonla
            foreach ($template->phases as $phase) {
                $newPhase = $project->phases()->create([
                    'name' => $phase->name,
                    'description' => $phase->description,
                    'sort_order' => $phase->sort_order,
                    'status' => 'pending',
                    'requires_ehs_approval' => $phase->requires_ehs_approval,
                ]);

                foreach ($phase->tasks as $task) {
                    $newPhase->tasks()->create([
                        'project_id' => $project->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => 'todo',
                        'priority' => $task->priority,
                        'tag' => $task->tag,
                        'estimated_hours' => $task->estimated_hours,
                        'is_milestone' => $task->is_milestone,
                        'sort_order' => $task->sort_order,
                    ]);
                }
            }

            // 2. Riskleri Klonla
            foreach ($template->risks as $risk) {
                $project->risks()->create([
                    'title' => $risk->title,
                    'description' => $risk->description,
                    'category' => $risk->category,
                    'probability' => $risk->probability,
                    'impact' => $risk->impact,
                    'risk_score' => $risk->risk_score,
                    'severity' => $risk->severity,
                    'status' => 'open',
                ]);
            }

            // 3. BOM Kalemlerini Klonla
            foreach ($template->boms as $bom) {
                $project->boms()->create([
                    'item_code' => $bom->item_code,
                    'item_name' => $bom->item_name,
                    'category' => $bom->category,
                    'quantity' => $bom->quantity,
                    'unit' => $bom->unit,
                    'unit_price' => $bom->unit_price,
                    'total_price' => $bom->total_price,
                    'currency' => $bom->currency,
                    'status' => 'planned',
                ]);
            }

            AuditLogService::log(
                'created',
                "Proje '{$template->title}' şablonundan oluşturuldu.",
                'Project',
                $project->id,
                $project->id
            );

            return $project;
        });
    }

    /**
     * Projeyi bir sonraki onay adımına veya onaycıya iletir ("Kimde Bekliyor?" radarı).
     */
    public function forwardToApproval(Project $project, int $waitingRoleId, ?int $waitingUserId = null, string $stepName = 'Onay'): void
    {
        $project->update([
            'status' => 'waiting_approval',
            'current_waiting_role_id' => $waitingRoleId,
            'current_waiting_user_id' => $waitingUserId,
            'waiting_since' => now(),
            'waiting_step_name' => $stepName,
        ]);

        AuditLogService::log(
            'status_changed',
            "Proje '{$stepName}' aşamasına gönderildi. Onay bekleniyor.",
            'Project',
            $project->id,
            $project->id
        );

        // Bildirim Matrisini tetikle
        app(NotificationMatrixService::class)->dispatch('proje_onaya_gonderildi', [
            'proje_adi' => $project->title,
            'rol' => $project->waitingRole?->name ?? 'Yetkili',
            'sure' => 'Yeni',
        ], route('projects.show', $project->id), $waitingUserId);
    }

    /**
     * Proje bilgilerini günceller ve audit log kaydı oluşturur.
     */
    public function updateProject(Project $project, array $data, $user = null): Project
    {
        return DB::transaction(function () use ($project, $data, $user) {
            $user = $user ?? auth()->user();
            $actorName = $user ? $user->name : 'Sistem';
            $notifService = app(NotificationMatrixService::class);
            $actionUrl = route('projects.show', $project->id);

            // 1. ESKİ DEĞERLER SNAPSHOT (Eski ve Yeni Karşılaştırma Analizi İçin)
            $oldTitle = $project->title;
            $oldCode = $project->code;
            $oldPriority = $project->priority ?? 'medium';
            $oldLocation = $project->location;
            $oldCategories = (array)($project->categories ?? ($project->category ? [$project->category] : []));
            $oldBolumIds = (array)($project->bolum_ids ?? ($project->bolum_id ? [$project->bolum_id] : []));
            $oldBolumNames = \App\Models\Bolum::whereIn('id', $oldBolumIds)->pluck('ad')->toArray();
            $oldManagerId = $project->manager_id;
            $oldManager = $project->manager;
            $oldSponsorId = $project->sponsor_id;
            $oldSponsor = $project->sponsor;
            $oldVisibility = $project->visibility ?? 'private';
            $oldBudget = (float)($project->planned_budget ?? 0);
            $oldCurrency = $project->currency ?? 'TRY';
            $oldStartDate = $project->start_date ? $project->start_date->format('Y-m-d') : null;
            $oldTargetEndDate = $project->target_end_date ? $project->target_end_date->format('Y-m-d') : null;
            $oldDescriptionClean = trim(strip_tags((string)$project->description));
            $oldScopeTools = (array)($project->scope_tools ?? []);

            $currentTeamStakeholders = $project->stakeholders()->where('role_type', 'team')->with('user')->get()->keyBy('user_id');
            $oldTeamUserIds = $currentTeamStakeholders->keys()->toArray();
            $oldTeamUserNames = $currentTeamStakeholders->map(fn($stk) => $stk->user?->name . ($stk->notes ? " ({$stk->notes})" : ''))->filter()->values()->toArray();

            $currentObserverStakeholders = $project->stakeholders()->where('role_type', 'observer')->with('user')->get()->keyBy('user_id');
            $oldObserverUserIds = $currentObserverStakeholders->keys()->toArray();
            $oldObserverUserNames = $currentObserverStakeholders->map(fn($stk) => $stk->user?->name)->filter()->values()->toArray();

            // 2. GELEN VERİLERİN AYRIŞTIRILMASI
            $teamMembersInput = $data['team_members'] ?? [];
            $observersInput = $data['observers'] ?? [];
            $externalObserversInput = $data['external_observers'] ?? [];
            $budgetItemsInput = $data['budget_items'] ?? [];

            $cats = $data['categories'] ?? [];
            if (empty($cats) && !empty($data['category'])) {
                $cats = [$data['category']];
            }
            if (empty($cats) && !empty($project->categories)) {
                $cats = (array)$project->categories;
            } elseif (empty($cats) && !empty($project->category)) {
                $cats = [$project->category];
            }
            $data['categories'] = array_values(array_filter((array)$cats));
            if (empty($data['categories'])) {
                $data['categories'] = ['genel'];
            }
            $data['category'] = $data['categories'][0];

            if (array_key_exists('tags', $data)) {
                $data['tags'] = is_array($data['tags']) ? array_values(array_filter($data['tags'])) : [];
            }

            $bolumler = $data['bolum_ids'] ?? [];
            if (empty($bolumler) && !empty($data['bolum_id'])) {
                $bolumler = [$data['bolum_id']];
            }
            $data['bolum_ids'] = array_values(array_map('intval', array_filter((array)$bolumler)));
            $data['bolum_id'] = !empty($data['bolum_ids']) ? $data['bolum_ids'][0] : null;

            unset($data['team_members'], $data['observers'], $data['external_observers'], $data['budget_items']);

            // Taslak güncelleme geçmişi ve yaşam döngüsü zaman damgası
            if ($project->status === 'draft') {
                $now = now();
                $history = $project->draft_history ?? [];
                if (empty($history)) {
                    $history[] = [
                        'action' => 'draft_created',
                        'user_id' => $project->created_by,
                        'user_name' => $project->creator?->name ?? 'Kullanıcı',
                        'timestamp' => ($project->draft_created_at ?? $project->created_at ?? $now)->toIso8601String(),
                        'note' => 'Taslak ilk kez oluşturuldu'
                    ];
                }
                $history[] = [
                    'action' => 'draft_updated',
                    'user_id' => $user?->id,
                    'user_name' => $actorName,
                    'timestamp' => $now->toIso8601String(),
                    'note' => 'Taslak detayları güncellendi'
                ];
                $data['draft_updated_at'] = $now;
                $data['draft_history'] = $history;
            }

            // 3. TEMEL PROJE KAYDINI GÜNCELLE
            $project->update($data);
            $project->refresh();

            $logNotes = [];

            // A. Başlık Değişimi
            if ($oldTitle !== $project->title) {
                $logNotes[] = "Başlık: '{$oldTitle}' ➔ '{$project->title}'";
            }

            // B. Öncelik Değişimi
            $priorityTr = ['low' => 'Düşük', 'medium' => 'Orta', 'high' => 'Yüksek', 'urgent' => 'Acil / Kritik'];
            if ($oldPriority !== $project->priority) {
                $oldPStr = $priorityTr[$oldPriority] ?? $oldPriority;
                $newPStr = $priorityTr[$project->priority] ?? $project->priority;
                $logNotes[] = "Öncelik: '{$oldPStr}' ➔ '{$newPStr}'";
            }

            // C. Kategori Değişimi
            $newCategories = (array)($project->categories ?? []);
            $diffCats1 = array_diff($oldCategories, $newCategories);
            $diffCats2 = array_diff($newCategories, $oldCategories);
            if (!empty($diffCats1) || !empty($diffCats2)) {
                $oldCStr = !empty($oldCategories) ? implode(', ', $oldCategories) : 'Yok';
                $newCStr = !empty($newCategories) ? implode(', ', $newCategories) : 'Yok';
                if ($oldCStr !== $newCStr) {
                    $logNotes[] = "Kategoriler: '{$oldCStr}' ➔ '{$newCStr}'";
                }
            }

            // D. Lokasyon / Saha Değişimi
            if (($oldLocation ?? '') !== ($project->location ?? '')) {
                $oldLocStr = $oldLocation ?: 'Belirtilmedi';
                $newLocStr = $project->location ?: 'Belirtilmedi';
                $logNotes[] = "Saha / Lokasyon: '{$oldLocStr}' ➔ '{$newLocStr}'";
            }

            // E. İlgili Bölümler Değişimi
            $newBolumIds = (array)($project->bolum_ids ?? []);
            $newBolumNames = \App\Models\Bolum::whereIn('id', $newBolumIds)->pluck('ad')->toArray();
            $diffB1 = array_diff($oldBolumIds, $newBolumIds);
            $diffB2 = array_diff($newBolumIds, $oldBolumIds);
            if (!empty($diffB1) || !empty($diffB2)) {
                $oldBStr = !empty($oldBolumNames) ? implode(', ', $oldBolumNames) : 'Yok';
                $newBStr = !empty($newBolumNames) ? implode(', ', $newBolumNames) : 'Yok';
                if ($oldBStr !== $newBStr) {
                    $logNotes[] = "İlgili Bölümler: '{$oldBStr}' ➔ '{$newBStr}'";
                }
            }

            // F. Liderlik & Sponsorluk Değişimi
            if ($oldManagerId != $project->manager_id) {
                if ($project->manager_id) {
                    ProjectStakeholder::updateOrCreate(
                        ['project_id' => $project->id, 'role_type' => 'manager'],
                        ['user_id' => $project->manager_id, 'raci_role' => 'A', 'notes' => 'Proje Yöneticisi / Lideri']
                    );
                    $notifService->dispatch('proje_yoneticisi_atandi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'sponsor' => $project->sponsor?->name ?? 'Belirtilmedi',
                        'butce' => number_format($project->planned_budget ?? 0, 2, ',', '.') . ' ' . $project->currency,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $project->manager_id);
                }
                $newManagerName = $project->manager?->name ?? 'Belirtilmedi';
                $oldManagerName = $oldManager?->name ?? 'Yok';
                $logNotes[] = "Proje Yöneticisi değiştirildi: {$oldManagerName} -> {$newManagerName}";
            }

            if ($oldSponsorId != $project->sponsor_id) {
                if ($project->sponsor_id) {
                    ProjectStakeholder::updateOrCreate(
                        ['project_id' => $project->id, 'role_type' => 'sponsor'],
                        ['user_id' => $project->sponsor_id, 'raci_role' => 'I', 'notes' => 'Proje Sponsoru (Üst Yönetim)']
                    );
                    $notifService->dispatch('proje_sponsoru_atandi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'yonetici' => $project->manager?->name ?? 'Belirtilmedi',
                        'butce' => number_format($project->planned_budget ?? 0, 2, ',', '.') . ' ' . $project->currency,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $project->sponsor_id);
                }
                $newSponsorName = $project->sponsor?->name ?? 'Belirtilmedi';
                $oldSponsorName = $oldSponsor?->name ?? 'Yok';
                $logNotes[] = "Proje Sponsoru değiştirildi: {$oldSponsorName} -> {$newSponsorName}";
            }

            // 5. EKİP ÜYELERİ SENKRONİZASYONU
            $newTeamUserIds = [];
            foreach ($teamMembersInput as $m) {
                $uId = (int)($m['user_id'] ?? 0);
                if (!$uId) continue;
                if ($uId == $project->manager_id || $uId == $project->sponsor_id) continue;

                $resp = $m['responsibility'] ?? 'Ekip Üyesi';
                if (($resp === 'Diğer' || str_starts_with($resp, 'Diğer')) && !empty($m['custom_responsibility'])) {
                    $resp = $m['custom_responsibility'];
                }

                $newTeamUserIds[] = $uId;

                $memberUser = User::find($uId);
                if (!$memberUser) continue;

                if (!in_array($uId, $oldTeamUserIds)) {
                    // YENİ EKLENEN EKİP ÜYESİ -> ZİL VE MAİL BİLDİRİMİ!
                    ProjectStakeholder::create([
                        'project_id' => $project->id,
                        'user_id' => $uId,
                        'role_type' => 'team',
                        'raci_role' => 'R',
                        'notes' => $resp,
                        'responsibility' => $resp,
                    ]);

                    $notifService->dispatch('projeye_dahil_edildi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'sorumluluk' => $resp,
                        'olusturan' => $actorName,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $uId);

                    $logNotes[] = "Yeni ekip üyesi eklendi: {$memberUser->name} ({$resp})";
                } else {
                    ProjectStakeholder::where('project_id', $project->id)
                        ->where('user_id', $uId)
                        ->where('role_type', 'team')
                        ->update(['notes' => $resp]);
                }
            }

            // Çıkarılan Ekip Üyeleri
            $removedTeamUserIds = array_diff($oldTeamUserIds, $newTeamUserIds);
            foreach ($removedTeamUserIds as $remId) {
                $remUser = User::find($remId);
                ProjectStakeholder::where('project_id', $project->id)
                    ->where('user_id', $remId)
                    ->where('role_type', 'team')
                    ->delete();

                if ($remUser) {
                    $notifService->dispatch('projeden_cikarildi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $remId);

                    $logNotes[] = "Ekip üyesi çıkarıldı: {$remUser->name}";
                }
            }

            // 6. GÖZLEMCİLER SENKRONİZASYONU
            $newObserverUserIds = array_map('intval', array_filter((array)$observersInput));
            $newObserverUserIds = array_diff($newObserverUserIds, array_filter([$project->manager_id, $project->sponsor_id]));

            $addedObserverIds = array_diff($newObserverUserIds, $oldObserverUserIds);
            foreach ($addedObserverIds as $addObsId) {
                $obsUser = User::find($addObsId);
                if (!$obsUser) continue;

                ProjectStakeholder::create([
                    'project_id' => $project->id,
                    'user_id' => $addObsId,
                    'role_type' => 'observer',
                    'raci_role' => 'I',
                    'notes' => 'Kurum İçi Gözlemci / Salt Okunur Takipçi',
                ]);

                $notifService->dispatch('yeni_proje_gozlemcisi', [
                    'proje_adi' => $project->title,
                    'proje_kodu' => $project->code,
                    'ekleyen' => $actorName,
                    'islem_yapan' => $actorName,
                ], $actionUrl, $addObsId);

                $logNotes[] = "Yeni gözlemci eklendi: {$obsUser->name}";
            }

            $removedObserverIds = array_diff($oldObserverUserIds, $newObserverUserIds);
            foreach ($removedObserverIds as $remObsId) {
                $remObsUser = User::find($remObsId);
                ProjectStakeholder::where('project_id', $project->id)
                    ->where('user_id', $remObsId)
                    ->where('role_type', 'observer')
                    ->delete();

                if ($remObsUser) {
                    $notifService->dispatch('proje_gozlemcisi_cikarildi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $remObsId);

                    $logNotes[] = "Gözlemci çıkarıldı: {$remObsUser->name}";
                }
            }

            // 7. GİZLİLİK SEVİYESİ DEĞİŞİMİ
            if ($oldVisibility != $project->visibility) {
                $visMap = ['public' => 'Genel (Açık)', 'private' => 'Gizli (Özel)'];
                $oldVisLabel = $visMap[$oldVisibility] ?? $oldVisibility;
                $newVisLabel = $visMap[$project->visibility] ?? $project->visibility;
                $logNotes[] = "Gizlilik: '{$oldVisLabel}' ➔ '{$newVisLabel}'";

                $raciUserIds = $project->stakeholders()->pluck('user_id')->filter()->unique()->toArray();
                foreach ($raciUserIds as $raciId) {
                    if ($raciId == $user?->id) continue;
                    $notifService->dispatch('proje_gizlilik_guncellendi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'yeni_gizlilik' => $newVisLabel,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $raciId);
                }
            }

            // 8. BÜTÇE, TAKVİM & KAPSAM DEĞİŞİMİ (Eski ➔ Yeni Karşılaştırmalı)
            $newBudget = (float)($project->planned_budget ?? 0);
            $budgetChanged = abs($oldBudget - $newBudget) > 0.01 || $oldCurrency != $project->currency;

            $newStartDate = $project->start_date ? $project->start_date->format('Y-m-d') : null;
            $startDateChanged = $oldStartDate != $newStartDate;

            $newTargetEndDate = $project->target_end_date ? $project->target_end_date->format('Y-m-d') : null;
            $endDateChanged = $oldTargetEndDate != $newTargetEndDate;

            if ($budgetChanged) {
                $oldBudgetFormatted = number_format($oldBudget, 2, ',', '.') . ' ' . $oldCurrency;
                $newBudgetFormatted = number_format($newBudget, 2, ',', '.') . ' ' . $project->currency;
                $logNotes[] = "Bütçe: {$oldBudgetFormatted} ➔ {$newBudgetFormatted}";
            }

            if ($startDateChanged) {
                $oldSDStr = $oldStartDate ? \Carbon\Carbon::parse($oldStartDate)->format('d.m.Y') : 'Belirtilmedi';
                $newSDStr = $newStartDate ? \Carbon\Carbon::parse($newStartDate)->format('d.m.Y') : 'Belirtilmedi';
                $logNotes[] = "Başlangıç Tarihi: {$oldSDStr} ➔ {$newSDStr}";
            }

            if ($endDateChanged) {
                $oldEDStr = $oldTargetEndDate ? \Carbon\Carbon::parse($oldTargetEndDate)->format('d.m.Y') : 'Belirtilmedi';
                $newEDStr = $newTargetEndDate ? \Carbon\Carbon::parse($newTargetEndDate)->format('d.m.Y') : 'Belirtilmedi';
                $logNotes[] = "Hedef Bitiş: {$oldEDStr} ➔ {$newEDStr}";
            }

            $newDescriptionClean = trim(strip_tags((string)$project->description));
            if ($oldDescriptionClean !== $newDescriptionClean && (!empty($oldDescriptionClean) || !empty($newDescriptionClean))) {
                $logNotes[] = "Kapsam Açıklaması güncellendi";
            }

            if ($budgetChanged || $endDateChanged) {
                $formattedBudget = number_format($newBudget, 2, ',', '.') . ' ' . $project->currency;
                $formattedDate = $project->target_end_date ? $project->target_end_date->format('d.m.Y') : 'Belirtilmedi';

                $raciUserIds = $project->stakeholders()->pluck('user_id')->filter()->unique()->toArray();
                foreach ($raciUserIds as $raciId) {
                    if ($raciId == $user?->id) continue;
                    $notifService->dispatch('proje_butce_takvim_guncellendi', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'butce' => $formattedBudget,
                        'hedef_tarih' => $formattedDate,
                        'islem_yapan' => $actorName,
                    ], $actionUrl, $raciId);
                }
            }

            // 9. BÜTÇE KALEMLERİ SENKRONİZASYONU
            if (!empty($budgetItemsInput) && is_array($budgetItemsInput)) {
                $project->budgetItems()->delete();
                $totalCalcBudget = 0;
                foreach ($budgetItemsInput as $bItem) {
                    if (!empty($bItem['title']) && isset($bItem['planned_amount']) && (float)$bItem['planned_amount'] > 0) {
                        $amt = (float)$bItem['planned_amount'];
                        $totalCalcBudget += $amt;
                        $catName = trim($bItem['category'] ?? 'Genel Yatırım');
                        $subCatName = !empty($bItem['sub_category']) ? trim($bItem['sub_category']) : null;
                        $catModel = \App\Models\ProjectBudgetCategory::where('name', $catName)->first();
                        $subCatModel = $subCatName ? \App\Models\ProjectBudgetCategory::where('name', $subCatName)->first() : null;

                        ProjectBudgetItem::create([
                            'project_id' => $project->id,
                            'title' => trim($bItem['title']),
                            'budget_type' => 'capex',
                            'category' => $catName,
                            'sub_category' => $subCatName,
                            'category_id' => $catModel?->id,
                            'sub_category_id' => $subCatModel?->id,
                            'planned_amount' => $amt,
                            'currency' => $project->currency,
                        ]);
                    }
                }
                if ($totalCalcBudget > 0 && empty($data['planned_budget'])) {
                    $project->planned_budget = $totalCalcBudget;
                    $project->save();
                }
            }

            // 9.1 KAPSAM ARAÇLARI (WIDGETLAR) FARKLILIKLARI & DENETİM LOGU
            $toolCatalog = [
                'budget_chart' => '📊 Bütçe Dağılım Grafiği',
                'kpis' => '🎯 SMART Hedefler & KPI',
                'scope_boundaries' => '📌 Kapsam Sınırları (Dahil / Hariç)',
                'roi_calculator' => '⚖️ ROI & Geri Dönüş Hesaplayıcı',
                'dependencies' => '🔗 Kritik Dış Bağımlılıklar',
                'custom_fields' => '🧩 Özel Parametreler',
            ];

            $isToolActive = function ($t, $k) {
                if (empty($t) || !is_array($t)) return false;
                if (!empty($t['enabled']) || !empty($t['active'])) return true;
                if ($k === 'budget_chart' && !empty($t['items']) && count($t['items']) > 0) return true;
                if ($k === 'kpis' && !empty($t['items']) && count($t['items']) > 0) return true;
                if ($k === 'scope_boundaries' && ((!empty($t['in_scope']) && count($t['in_scope']) > 0) || (!empty($t['out_scope']) && count($t['out_scope']) > 0))) return true;
                if ($k === 'roi_calculator' && (!empty($t['annual_savings']) || !empty($t['investment_cost']) || !empty($t['payback_months']))) return true;
                if ($k === 'dependencies' && !empty($t['items']) && count($t['items']) > 0) return true;
                if ($k === 'custom_fields' && !empty($t['items']) && count($t['items']) > 0) return true;
                return false;
            };

            $newScopeTools = (array)($project->scope_tools ?? []);
            foreach ($toolCatalog as $tKey => $tLabel) {
                $wasActive = $isToolActive($oldScopeTools[$tKey] ?? null, $tKey);
                $isActive = $isToolActive($newScopeTools[$tKey] ?? null, $tKey);

                if (!$wasActive && $isActive) {
                    $logNotes[] = "Kapsam Aracı Eklendi: {$tLabel}";
                } elseif ($wasActive && !$isActive) {
                    $logNotes[] = "Kapsam Aracı Kaldırıldı: {$tLabel}";
                } elseif ($wasActive && $isActive) {
                    $oldHash = md5(json_encode($oldScopeTools[$tKey] ?? []));
                    $newHash = md5(json_encode($newScopeTools[$tKey] ?? []));
                    if ($oldHash !== $newHash) {
                        $logNotes[] = "Kapsam Aracı Güncellendi: {$tLabel}";
                    }
                }
            }

            $formatToolsSummary = function ($tools) use ($toolCatalog, $isToolActive) {
                if (empty($tools) || !is_array($tools)) return 'Hiçbir araç tanımlanmadı';
                $lines = [];
                foreach ($toolCatalog as $k => $label) {
                    $t = $tools[$k] ?? null;
                    if (!$isToolActive($t, $k)) continue;
                    if ($k === 'budget_chart') {
                        $c = count($t['items'] ?? []);
                        $lines[] = "{$label} ({$c} Kalem)";
                    } elseif ($k === 'kpis') {
                        $c = count($t['items'] ?? []);
                        $lines[] = "{$label} ({$c} Hedef)";
                    } elseif ($k === 'scope_boundaries') {
                        $in = count($t['in_scope'] ?? []);
                        $out = count($t['out_scope'] ?? []);
                        $lines[] = "{$label} ({$in} Dahil / {$out} Hariç)";
                    } elseif ($k === 'roi_calculator') {
                        $pb = !empty($t['payback_years']) ? $t['payback_years'] . ' Yıl' : ($t['payback_months'] ?? '-') . ' Ay';
                        $lines[] = "{$label} ({$pb} Geri Dönüş, %" . ($t['roi_percentage'] ?? '0') . " ROI)";
                    } elseif ($k === 'dependencies') {
                        $c = count($t['items'] ?? []);
                        $lines[] = "{$label} ({$c} Bağımlılık)";
                    } elseif ($k === 'custom_fields') {
                        $c = count($t['items'] ?? []);
                        $lines[] = "{$label} ({$c} Parametre)";
                    }
                }
                return !empty($lines) ? implode(' | ', $lines) : 'Hiçbir araç tanımlanmadı';
            };

            // 10. DENETİM LOGU (AUDIT LOG - Eski ve Yeni Veri Karşılaştırmalı & Madde 16 Vekalet Desteği)
            $logSummary = !empty($logNotes) 
                ? "Proje Düzenlendi: '{$project->code} - {$project->title}'. " . implode(' | ', $logNotes)
                : "Proje bilgileri güncellendi: '{$project->code} - {$project->title}'";

            // Madde 16: Vekalet / Temsil Tespiti
            $activeDelegation = $user ? $user->getActiveReceivedDelegations(null, $project->id)->first() : null;
            $actingAsUser = $activeDelegation?->delegator;
            $actingAsRoleName = $activeDelegation ? $activeDelegation->delegator->name . ' (Vekaleten: ' . $user->name . ')' : null;

            if ($actingAsUser) {
                $logSummary = "**{$user->name}** tarafından **{$actingAsUser->name}** adına " . lcfirst($logSummary);
            }

            $newTeamStakeholders = $project->stakeholders()->where('role_type', 'team')->with('user')->get();
            $newTeamUserNames = $newTeamStakeholders->map(fn($stk) => $stk->user?->name . ($stk->notes ? " ({$stk->notes})" : ''))->filter()->values()->toArray();

            $newObserverStakeholders = $project->stakeholders()->where('role_type', 'observer')->with('user')->get();
            $newObserverUserNames = $newObserverStakeholders->map(fn($stk) => $stk->user?->name)->filter()->values()->toArray();

            $oldValues = [
                'title' => $oldTitle,
                'priority' => $priorityTr[$oldPriority] ?? $oldPriority,
                'categories' => $oldCategories,
                'bolumler' => $oldBolumNames,
                'location' => $oldLocation ?: 'Belirtilmedi',
                'manager' => $oldManager?->name ?? 'Yok',
                'sponsor' => $oldSponsor?->name ?? 'Yok',
                'planned_budget' => number_format($oldBudget, 2, ',', '.') . ' ' . $oldCurrency,
                'start_date' => $oldStartDate ? \Carbon\Carbon::parse($oldStartDate)->format('d.m.Y') : 'Belirtilmedi',
                'target_end_date' => $oldTargetEndDate ? \Carbon\Carbon::parse($oldTargetEndDate)->format('d.m.Y') : 'Belirtilmedi',
                'visibility' => ($oldVisibility === 'public') ? 'Genel (Açık)' : 'Gizli (Özel)',
                'team_members' => $oldTeamUserNames,
                'observers' => $oldObserverUserNames,
                'scope_tools' => $formatToolsSummary($oldScopeTools),
                'tags' => !empty($oldTags) ? implode(', ', $oldTags) : 'Etiketsiz',
            ];

            $newValues = [
                'title' => $project->title,
                'priority' => $priorityTr[$project->priority] ?? $project->priority,
                'categories' => $newCategories,
                'bolumler' => $newBolumNames,
                'location' => $project->location ?: 'Belirtilmedi',
                'manager' => $project->manager?->name ?? 'Yok',
                'sponsor' => $project->sponsor?->name ?? 'Yok',
                'planned_budget' => number_format($newBudget, 2, ',', '.') . ' ' . $project->currency,
                'start_date' => $newStartDate ? \Carbon\Carbon::parse($newStartDate)->format('d.m.Y') : 'Belirtilmedi',
                'target_end_date' => $newTargetEndDate ? \Carbon\Carbon::parse($newTargetEndDate)->format('d.m.Y') : 'Belirtilmedi',
                'visibility' => ($project->visibility === 'public') ? 'Genel (Açık)' : 'Gizli (Özel)',
                'team_members' => $newTeamUserNames,
                'observers' => $newObserverUserNames,
                'scope_tools' => $formatToolsSummary($newScopeTools),
                'tags' => !empty($newTags) ? implode(', ', $newTags) : 'Etiketsiz',
            ];

            AuditLogService::log(
                'updated',
                $logSummary,
                'Project',
                $project->id,
                $project->id,
                $oldValues,
                $newValues,
                $actingAsUser,
                $actingAsRoleName
            );

            return $project;
        });
    }

    /**
     * Projeyi güvenli bir şekilde siler (SoftDelete) ve audit log kaydı oluşturur.
     */
    public function deleteProject(Project $project, $user = null): bool
    {
        $code = $project->code;
        $title = $project->title;
        $id = $project->id;
        $user = $user ?? auth()->user();
        $userName = $user ? $user->name : 'Sistem';

        $snapshot = [
            'id' => $id,
            'code' => $code,
            'title' => $title,
            'status' => $project->status,
            'budget' => $project->budget,
            'currency' => $project->currency,
            'manager_id' => $project->manager_id,
            'sponsor_id' => $project->sponsor_id,
            'deleted_by_user_id' => $user?->id,
            'deleted_by_user_name' => $userName,
        ];

        $deleted = $project->delete();

        if ($deleted) {
            AuditLogService::log(
                'deleted',
                "Proje Silindi (Arşive Alındı): '{$code} - {$title}' (#{$id}) - İşlemi Yapan: {$userName}",
                'Project',
                $id,
                $id,
                $snapshot,
                null
            );
        }

        return (bool) $deleted;
    }
}

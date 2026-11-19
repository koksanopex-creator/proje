<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Http\Controllers\Admin\NotificationMatrixController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoleGroupController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Admin\ProjectTeamRoleController;
use App\Http\Controllers\Admin\ProjectBudgetCategoryController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\DoaThresholdController;
use App\Http\Controllers\Admin\CommitteeController;
use App\Http\Controllers\Auth\SsoController;

// Kimlik Doğrulama (Auth) & Merkezi SSO Rotaları
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::any('/logout', [SsoController::class, 'logout'])->name('logout');
Route::get('/sso/redirect', [SsoController::class, 'redirectToCentral'])->name('sso.redirect');
Route::get('/sso/login', [SsoController::class, 'login'])->name('sso.login');
Route::get('/switch-user/{id}', [AuthController::class, 'switchUser'])->name('switch_user');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

// Dış Paydaş Taslak Proje İnceleme (Geçiş Anahtarı & Token ile Korumalı)
Route::get('/taslak-proje/{project}/harici-inceleme', [ProjectController::class, 'externalDraftReview'])->name('projects.external_draft_review');
Route::post('/taslak-proje/{project}/harici-oneri', [ProjectController::class, 'storeExternalDraftSuggestion'])->name('projects.store_external_draft_suggestion');
Route::get('/taslak-proje/{project}/dosya/{file}/indir', [ProjectController::class, 'downloadExternalFile'])->name('projects.external_download_file');

// Merkezi API Otomatik Kullanıcı Senkronizasyon Webhook Uç Noktası
Route::post('/api/central/sync-user', [UserRoleController::class, 'handleWebhook'])->name('api.central.sync_user');

// Korumalı Rotalar
Route::middleware('auth')->group(function () {
    Route::post('/notifications/mark-all-read', function () {
        auth()->user()->inAppNotifications()->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('notifications.mark_all_read');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/calendar-events', [DashboardController::class, 'getCalendarEvents'])->name('dashboard.calendar_events');
    Route::post('/dashboard/calendar-notes', [DashboardController::class, 'storeCalendarNote'])->name('dashboard.calendar_notes.store');
    Route::put('/dashboard/calendar-notes/{id}', [DashboardController::class, 'updateCalendarNote'])->name('dashboard.calendar_notes.update');
    Route::delete('/dashboard/calendar-notes/{id}', [DashboardController::class, 'destroyCalendarNote'])->name('dashboard.calendar_notes.destroy');

    // Global Arama & Popüler Etiketler (Ctrl + K)
    Route::get('/global-search', [GlobalSearchController::class, 'search'])->name('global_search');
    Route::get('/global-search/popular-tags', [GlobalSearchController::class, 'popularTags'])->name('global_search.tags');

    // Projeler
    Route::get('/projects/templates/{id}/json', [ProjectController::class, 'getTemplateJson'])->name('projects.template_json');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/toggle-visibility', [ProjectController::class, 'toggleVisibility'])->name('projects.toggle_visibility');
    // Proje Gözlemci (Observer) Rotaları
    Route::post('/projects/{project}/observers', [ProjectController::class, 'addObserver'])->name('projects.add_observer');
    Route::delete('/projects/{project}/observers/{stakeholder}', [ProjectController::class, 'removeObserver'])->name('projects.remove_observer');

    // Proje Adımları (Fazlar) & Dosya/Envanter Rotaları
    Route::post('/projects/{project}/phases', [ProjectController::class, 'addPhase'])->name('projects.add_phase');
    Route::post('/projects/{project}/tasks', [ProjectController::class, 'addTask'])->name('projects.add_task');
    Route::post('/projects/{project}/tasks/{task}/status', [ProjectController::class, 'updateTaskStatus'])->name('projects.update_task_status');
    Route::delete('/projects/{project}/tasks/{task}', [ProjectController::class, 'deleteTask'])->name('projects.delete_task');
    Route::post('/projects/{project}/phases/{phase}/complete', [ProjectController::class, 'completePhase'])->name('projects.complete_phase');
    Route::post('/projects/{project}/phases/{phase}/revert', [ProjectController::class, 'revertPhase'])->name('projects.revert_phase');
    Route::post('/projects/{project}/phases/{phase}/revert-ehs', [ProjectController::class, 'revertEhsGate'])->name('projects.revert_ehs');
    Route::post('/projects/{project}/revert-approval', [ProjectController::class, 'revertApproval'])->name('projects.revert_approval');
    Route::post('/projects/{project}/files', [ProjectController::class, 'uploadFile'])->name('projects.upload_file');
    Route::post('/projects/{project}/files/{file}/tags', [ProjectController::class, 'updateFileTags'])->name('projects.files.update_tags');
    Route::delete('/projects/{project}/files/{file}', [ProjectController::class, 'deleteFile'])->name('projects.delete_file');
    Route::get('/projects/{project}/files/{file}/download', [ProjectController::class, 'downloadFile'])->name('projects.download_file');
    Route::post('/projects/{project}/discussions', [ProjectController::class, 'storeDiscussion'])->name('projects.discussions.store');
    Route::put('/projects/{project}/discussions/{discussion}', [ProjectController::class, 'updateDiscussion'])->name('projects.discussions.update');
    Route::delete('/projects/{project}/discussions/{discussion}', [ProjectController::class, 'deleteDiscussion'])->name('projects.discussions.delete');
    Route::post('/projects/{project}/phases/{phase}/approve-ehs', [ProjectController::class, 'approveEhsGate'])->name('projects.approve_ehs');
    Route::post('/projects/{project}/submit-approval', [ProjectController::class, 'submitForApproval'])->name('projects.submit_approval');
    Route::post('/projects/{project}/approve', [ProjectController::class, 'approve'])->name('projects.approve');
    Route::post('/projects/{project}/committee-vote', [ProjectController::class, 'castCommitteeVote'])->name('projects.committee_vote');
    Route::post('/projects/{project}/share-draft', [ProjectController::class, 'shareDraft'])->name('projects.share_draft');
    Route::post('/projects/{project}/draft-shares/{share}/revoke', [ProjectController::class, 'revokeDraftShare'])->name('projects.draft_shares.revoke');
    Route::post('/projects/{project}/draft-suggestions', [ProjectController::class, 'storeDraftSuggestion'])->name('projects.store_draft_suggestion');
    Route::post('/projects/{project}/publish-draft', [ProjectController::class, 'publishDraft'])->name('projects.publish_draft');
    // Proje İçi Ekip Yetki Matrisi & Devir (Handover) & Lider Değiştirme
    Route::post('/projects/{project}/stakeholders/permission', [ProjectController::class, 'toggleStakeholderPermission'])->name('projects.stakeholders.permission');
    Route::post('/projects/{project}/stakeholders/handover', [ProjectController::class, 'handoverMember'])->name('projects.stakeholders.handover');
    Route::post('/projects/{project}/change-manager', [ProjectController::class, 'changeManager'])->name('projects.change_manager');

    // Kurumsal Vekalet (User Deputyship / Proxy) Rotaları
    Route::get('/delegations', [DelegationController::class, 'index'])->name('delegations.index');
    Route::get('/delegations/{id}', [DelegationController::class, 'show'])->name('delegations.show');
    Route::post('/delegations', [DelegationController::class, 'store'])->name('delegations.store');
    Route::delete('/delegations/{id}', [DelegationController::class, 'destroy'])->name('delegations.destroy');

    // Kanban & Gantt
    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('/kanban/update-status', [KanbanController::class, 'updateStatus'])->name('kanban.update_status');
    Route::get('/gantt', [GanttController::class, 'index'])->name('gantt.index');
    Route::get('/gantt/data/{id}', [GanttController::class, 'data'])->name('gantt.data');
    Route::get('/gantt/{id}/export-excel', [GanttController::class, 'exportExcel'])->name('gantt.export_excel');

    // Kaynaklar (Kapasite Isı Haritası)
    Route::get('/kaynaklar', [ResourceController::class, 'index'])->name('resources.index');

    // Risk Yönetimi (5x5 Matris)
    Route::get('/riskler', [RiskController::class, 'index'])->name('risks.index');
    Route::post('/riskler', [RiskController::class, 'store'])->name('risks.store');
    Route::post('/riskler/{id}/mitigation', [RiskController::class, 'addMitigation'])->name('risks.mitigation');

    // Zaman Takibi (Timesheet)
    Route::get('/zaman-takibi', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('/zaman-takibi', [TimesheetController::class, 'store'])->name('timesheets.store');
    Route::post('/zaman-takibi/{id}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');

    // BOM Malzeme Ağacı & Bütçe
    Route::get('/butce-bom', [BomController::class, 'index'])->name('boms.index');
    Route::post('/butce-bom/item', [BomController::class, 'storeBom'])->name('boms.store_bom');
    Route::post('/butce-bom/budget', [BomController::class, 'storeBudget'])->name('boms.store_budget');

    // Toplantılar
    Route::get('/toplantilar', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/toplantilar', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/toplantilar/{id}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::put('/toplantilar/{id}/status', [MeetingController::class, 'updateStatus'])->name('meetings.update_status');
    Route::put('/toplantilar/{id}/agenda', [MeetingController::class, 'updateAgenda'])->name('meetings.update_agenda');
    Route::post('/toplantilar/{id}/convert-task', [MeetingController::class, 'convertActionToTask'])->name('meetings.convert_task');
    Route::post('/toplantilar/{id}/participants', [MeetingController::class, 'addParticipant'])->name('meetings.add_participant');
    Route::delete('/toplantilar/{id}/participants/{participantId}', [MeetingController::class, 'removeParticipant'])->name('meetings.remove_participant');

    // 5 Farklı Rapor Tipi
    Route::get('/raporlar', [ReportController::class, 'index'])->name('reports.index');

    // Admin & Superadmin Yönetim Panelleri (AdminMiddleware ile tam korumalı)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/yetki-matrisi', [PermissionMatrixController::class, 'index'])->name('permission_matrix');
        // Rol & Etiket Renk Yonetimi
        Route::get('/roller', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roller', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roller/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roller/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        // Kullanıcı & Çoklu Rol Yetkilendirme Paneli
        Route::get('/kullanici-rolleri', [UserRoleController::class, 'index'])->name('user_roles.index');
        Route::post('/kullanici-rolleri', [UserRoleController::class, 'storeUser'])->name('user_roles.store');
        Route::post('/kullanici-rolleri/{id}/roller', [UserRoleController::class, 'updateRoles'])->name('user_roles.update');
        Route::post('/kullanici-rolleri/{id}/toggle-rol', [UserRoleController::class, 'toggleRole'])->name('user_roles.toggle_role');
        Route::post('/kullanici-rolleri/sync', [UserRoleController::class, 'syncFromCentral'])->name('user_roles.sync');
        // Dinamik Rol Grupları Yönetimi
        Route::get('/rol-gruplari', [RoleGroupController::class, 'index'])->name('role_groups.index');
        Route::post('/rol-gruplari', [RoleGroupController::class, 'store'])->name('role_groups.store');
        Route::put('/rol-gruplari/{id}', [RoleGroupController::class, 'update'])->name('role_groups.update');
        Route::delete('/rol-gruplari/{id}', [RoleGroupController::class, 'destroy'])->name('role_groups.destroy');
        Route::post('/yetki-matrisi/toggle', [PermissionMatrixController::class, 'toggle'])->name('permission_matrix.toggle');
        Route::post('/yetki-matrisi/reset-defaults', [PermissionMatrixController::class, 'resetToDefaults'])->name('permission_matrix.reset_defaults');

        Route::get('/bildirim-matrisi', [NotificationMatrixController::class, 'index'])->name('notification_matrix');
        Route::post('/bildirim-matrisi/toggle', [NotificationMatrixController::class, 'toggle'])->name('notification_matrix.toggle');
        Route::post('/bildirim-sablonlari', [NotificationMatrixController::class, 'storeTemplate'])->name('notification_templates.store');
        Route::put('/bildirim-sablonlari/{id}', [NotificationMatrixController::class, 'updateTemplate'])->name('notification_templates.update');
        Route::post('/sync-bolumler', [NotificationMatrixController::class, 'syncBolumler'])->name('sync_bolumler');
        // Kategori Yönetimi CRUD
        Route::get('/kategoriler', [ProjectCategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategoriler', [ProjectCategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategoriler/{id}', [ProjectCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategoriler/{id}', [ProjectCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/kategoriler/{id}/delete', [ProjectCategoryController::class, 'destroy'])->name('categories.destroy_post');

        // Ekip Sorumluluk Rolleri CRUD
        Route::get('/team-roles', [ProjectTeamRoleController::class, 'index'])->name('team_roles.index');
        Route::post('/team-roles', [ProjectTeamRoleController::class, 'store'])->name('team_roles.store');
        Route::put('/team-roles/{id}', [ProjectTeamRoleController::class, 'update'])->name('team_roles.update');
        Route::delete('/team-roles/{id}', [ProjectTeamRoleController::class, 'destroy'])->name('team_roles.destroy');
        Route::post('/team-roles/{id}/delete', [ProjectTeamRoleController::class, 'destroy'])->name('team_roles.destroy_post');

        // Harcama Kategorileri CRUD (Bütçe Kalemleri Kırılımı)
        Route::get('/budget-categories', [ProjectBudgetCategoryController::class, 'index'])->name('budget_categories.index');
        Route::post('/budget-categories', [ProjectBudgetCategoryController::class, 'store'])->name('budget_categories.store');
        Route::put('/budget-categories/{id}', [ProjectBudgetCategoryController::class, 'update'])->name('budget_categories.update');
        Route::delete('/budget-categories/{id}', [ProjectBudgetCategoryController::class, 'destroy'])->name('budget_categories.destroy');
        Route::post('/budget-categories/{id}/delete', [ProjectBudgetCategoryController::class, 'destroy'])->name('budget_categories.destroy_post');

        // Para Birimleri CRUD
        Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::post('/currencies', [CurrencyController::class, 'store'])->name('currencies.store');
        Route::put('/currencies/{id}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::delete('/currencies/{id}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');
        Route::post('/currencies/{id}/delete', [CurrencyController::class, 'destroy'])->name('currencies.destroy_post');

        // Dinamik Bütçe Eşik Kademeleri (DOA Matrix)
        Route::get('/doa-esikleri', [DoaThresholdController::class, 'index'])->name('doa_thresholds.index');
        Route::post('/doa-esikleri', [DoaThresholdController::class, 'store'])->name('doa_thresholds.store');
        Route::put('/doa-esikleri/{id}', [DoaThresholdController::class, 'update'])->name('doa_thresholds.update');
        Route::delete('/doa-esikleri/{id}', [DoaThresholdController::class, 'destroy'])->name('doa_thresholds.destroy');
        Route::post('/doa-esikleri/{id}/toggle', [DoaThresholdController::class, 'toggleActive'])->name('doa_thresholds.toggle');
        Route::post('/doa-esikleri/toggle-global-engine', [DoaThresholdController::class, 'toggleGlobalEngine'])->name('doa_thresholds.toggle_engine');
        Route::post('/doa-esikleri/simulate', [DoaThresholdController::class, 'simulate'])->name('doa_thresholds.simulate');

        // Kurullar ve Komiteler Yönetimi
        Route::get('/kurullar', [CommitteeController::class, 'index'])->name('committees.index');
        Route::post('/kurullar', [CommitteeController::class, 'store'])->name('committees.store');
        Route::put('/kurullar/{id}', [CommitteeController::class, 'update'])->name('committees.update');
        Route::delete('/kurullar/{id}', [CommitteeController::class, 'destroy'])->name('committees.destroy');
        Route::post('/kurullar/{id}/toggle', [CommitteeController::class, 'toggleActive'])->name('committees.toggle');
        Route::post('/kurullar/{id}/members', [CommitteeController::class, 'addMember'])->name('committees.members.store');
        Route::delete('/kurullar/{id}/members/{memberId}', [CommitteeController::class, 'removeMember'])->name('committees.members.destroy');
        Route::post('/kurullar/{id}/members/{memberId}/toggle', [CommitteeController::class, 'toggleMember'])->name('committees.members.toggle');

        Route::get('/sistem-sagligi', [SystemHealthController::class, 'index'])->name('system_health');
        Route::post('/sistem-sagligi/retest', [SystemHealthController::class, 'retest'])->name('system_health.retest');

        Route::get('/denetim-loglari', [AuditLogController::class, 'index'])->name('audit_logs');
        Route::post('/denetim-loglari/toggle-visibility', [AuditLogController::class, 'toggleVisibility'])->name('audit_logs.toggle_visibility');

        Route::get('/ayarlar', [SettingController::class, 'index'])->name('settings');
        Route::post('/ayarlar', [SettingController::class, 'update'])->name('settings.update');
    });
});

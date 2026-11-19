<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Services\AuditLogService;
use App\Services\DepartmentSyncService;

class NotificationMatrixController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::where('is_active', true)->get();
        $roles = Role::all();
        $roleGroups = \App\Models\RoleGroup::orderBy('sort_order')->get();
        $matrix = NotificationMatrix::all();

        return view('admin.notification_matrix', compact('templates', 'roles', 'matrix', 'roleGroups'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'event_key' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'channel' => 'required|in:bell,mail',
            'state' => 'required|boolean',
        ]);

        $entry = NotificationMatrix::firstOrNew([
            'event_key' => $request->event_key,
            'role_id' => $request->role_id,
        ]);

        if ($request->channel === 'bell') {
            $entry->is_bell_active = $request->state;
        } else {
            $entry->is_mail_active = $request->state;
        }
        $entry->save();

        AuditLogService::log(
            'updated',
            "Bildirim Matrisi güncellendi: '{$request->event_key}' olayı için Rol ID: {$request->role_id} {$request->channel} kanalı " . ($request->state ? 'açıldı' : 'kapatıldı') . '.',
            'NotificationMatrix',
            $entry->id
        );

        return response()->json(['success' => true]);
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $request->validate([
            'event_title' => 'required|string|max:150',
            'bell_title_template' => 'required|string|max:255',
            'bell_body_template' => 'required|string',
            'mail_subject_template' => 'required|string|max:255',
            'mail_body_template' => 'required|string',
        ]);

        $template->update([
            'event_title' => $request->event_title,
            'bell_title_template' => $request->bell_title_template,
            'bell_body_template' => $request->bell_body_template,
            'mail_subject_template' => $request->mail_subject_template,
            'mail_body_template' => $request->mail_body_template,
        ]);

        AuditLogService::log(
            'updated',
            "Bildirim Taslağı güncellendi: '{$template->event_title}' ({$template->event_key}).",
            'NotificationTemplate',
            $template->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'template' => $template,
                'message' => 'Bildirim taslağı başarıyla güncellendi.',
            ]);
        }

        return redirect()->back()->with('success', 'Bildirim taslağı güncellendi.');
    }

    public function syncBolumler(Request $request)
    {
        $result = DepartmentSyncService::syncFromCentral();
        return response()->json($result);
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'event_key' => 'required|string|max:100|unique:notification_templates,event_key',
            'event_title' => 'required|string|max:150',
            'category' => 'required|string|max:50',
            'bell_title_template' => 'required|string|max:255',
            'bell_body_template' => 'required|string',
            'mail_subject_template' => 'required|string|max:255',
            'mail_body_template' => 'required|string',
        ]);

        $template = NotificationTemplate::create([
            'event_key' => \Illuminate\Support\Str::slug($request->event_key, '_'),
            'event_title' => $request->event_title,
            'category' => $request->category,
            'bell_title_template' => $request->bell_title_template,
            'bell_body_template' => $request->bell_body_template,
            'mail_subject_template' => $request->mail_subject_template,
            'mail_body_template' => $request->mail_body_template,
            'available_variables' => ['proje_adi', 'proje_kodu', 'kullanici_adi', 'faz_adi', 'gorev_adi', 'olusturan'],
            'is_active' => true,
        ]);

        $roles = Role::all();
        $roleGroups = \App\Models\RoleGroup::orderBy('sort_order')->get();
        foreach ($roles as $role) {
            NotificationMatrix::firstOrCreate([
                'event_key' => $template->event_key,
                'role_id' => $role->id,
            ], [
                'is_bell_active' => true,
                'is_mail_active' => true,
                'template_id' => $template->id,
            ]);
        }

        AuditLogService::log(
            'created',
            "Yeni Bildirim Taslağı eklendi: '{$template->event_title}' ({$template->event_key}).",
            'NotificationTemplate',
            $template->id
        );

        return redirect()->back()->with('success', "Yeni bildirim taslağı '{$template->event_title}' başarıyla oluşturuldu ve tüm rollere eklendi.");
    }

}

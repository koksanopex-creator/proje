<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAuditLog extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'acting_as_user_id', 'acting_as_role_name',
        'entity_type', 'entity_id', 'action', 'description', 'old_values', 'new_values',
        'ip_address', 'user_agent'
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function actingAsUser() { return $this->belongsTo(User::class, 'acting_as_user_id'); }

    // Madde 14 & 16: Maskeleme Destekli Kullanıcı Gösterimi
    public function getActorNameAttribute(): string
    {
        $viewer = auth()->user();
        $realUser = $this->user;
        if (!$realUser) return 'Sistem';

        $actorName = $realUser->getMaskedNameFor($viewer);

        if ($this->acting_as_user_id && $this->acting_as_role_name) {
            return "{$actorName} ({$this->acting_as_role_name} adına)";
        }

        return $actorName;
    }

    public function getFormattedDescriptionAttribute(): string
    {
        $text = e($this->description);

        // Dinamik İzin/Slug Dönüştürücü: Eğer açıklamada sistem izin slug'ları varsa matris Türkçe tanımlarına çevir
        if (str_contains($text, 'proje.') || str_contains($text, 'task.') || str_contains($text, 'timesheet.') || str_contains($text, 'butce.') || str_contains($text, 'meeting.') || str_contains($text, 'isg.') || str_contains($text, 'manage_') || str_contains($text, 'vote_') || str_contains($text, 'kanban.')) {
            $catalog = UserDelegation::getScopeCatalog();
            // Slug uzunluğuna göre azalan sırala (alt stringlerin çakışmaması için)
            uksort($catalog, fn($a, $b) => strlen($b) <=> strlen($a));
            foreach ($catalog as $slug => $name) {
                if (str_contains($text, $slug)) {
                    $text = str_replace($slug, $name, $text);
                }
            }
        }

        // Markdown **bold** ifadelerini <strong> ile güvenle biçimlendir
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        return $text;
    }

    public function getDiffsAttribute(): array
    {
        if (empty($this->old_values) || empty($this->new_values)) {
            return [];
        }

        $fieldLabels = [
            'title' => 'Proje Başlığı',
            'priority' => 'Öncelik Seviyesi',
            'categories' => 'Kategoriler',
            'bolumler' => 'İlgili Bölümler',
            'location' => 'Saha / Lokasyon',
            'manager' => 'Proje Lideri',
            'sponsor' => 'Proje Sponsoru',
            'planned_budget' => 'Planlanan Bütçe',
            'start_date' => 'Başlangıç Tarihi',
            'target_end_date' => 'Hedef Bitiş Tarihi',
            'actual_end_date' => 'Gerçekleşen Bitiş',
            'visibility' => 'Gizlilik Seviyesi',
            'team_members' => 'Ekip Kadrosu',
            'observers' => 'Gözlemciler',
            'budget' => 'Planlanan Bütçe',
            'currency' => 'Para Birimi',
            'scope_tools' => 'Kapsam Araçları (Widgetlar)',
            'tags' => 'Proje Etiketleri',
        ];

        $diffs = [];
        foreach ($this->new_values as $k => $newVal) {
            $cleanKey = preg_replace('/^(new_|old_)/', '', $k);
            if (isset($diffs[$cleanKey])) continue;

            $oldVal = $this->old_values[$cleanKey] 
                   ?? ($this->old_values['old_' . $cleanKey] ?? ($this->old_values[$k] ?? null));

            // Sayısal bütçe değerlerini formatla
            if (str_contains($cleanKey, 'budget') && is_numeric($oldVal) && is_numeric($newVal)) {
                $curr = $this->new_values['currency'] ?? ($this->new_values['new_currency'] ?? ($this->old_values['old_currency'] ?? 'TRY'));
                $oldStr = number_format((float)$oldVal, 2, ',', '.') . ' ' . $curr;
                $newStr = number_format((float)$newVal, 2, ',', '.') . ' ' . $curr;
            } else {
                $oldStr = is_array($oldVal) ? implode(', ', $oldVal) : (string)($oldVal ?? '');
                $newStr = is_array($newVal) ? implode(', ', $newVal) : (string)($newVal ?? '');
            }

            if ($oldStr !== $newStr && $oldVal !== null) {
                $diffs[$cleanKey] = [
                    'label' => $fieldLabels[$cleanKey] ?? \Illuminate\Support\Str::headline($cleanKey),
                    'old' => $oldStr ?: 'Belirtilmedi',
                    'new' => $newStr ?: 'Belirtilmedi',
                ];
            }
        }

        return array_values($diffs);
    }

    /**
     * Tüm sistem aksiyonları için standart kurumsal renk, ikon ve Türkçe başlık kataloğu (Tek Merkez)
     */
    public static function getActionCatalog(): array
    {
        return [
            'created' => ['title' => 'Oluşturuldu', 'icon' => '✨', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
            'updated' => ['title' => 'Güncellendi', 'icon' => '✏️', 'bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],
            'deleted' => ['title' => 'Silindi', 'icon' => '🗑️', 'bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca'],
            'approved' => ['title' => 'Onaylandı', 'icon' => '🛡️', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
            'rejected' => ['title' => 'Reddedildi', 'icon' => '❌', 'bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#f87171'],
            'status_changed' => ['title' => 'Durum Değişti', 'icon' => '🔄', 'bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
            'gatekeeper_approval' => ['title' => 'İSG Kapı Onayı', 'icon' => '🛡️', 'bg' => '#f5f3ff', 'text' => '#5b21b6', 'border' => '#ddd6fe'],
            'completed' => ['title' => 'Tamamlandı', 'icon' => '✅', 'bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'],
            'reverted' => ['title' => 'Geri Alındı', 'icon' => '↩️', 'bg' => '#fff1f2', 'text' => '#9f1239', 'border' => '#fecdd3'],
            'commented' => ['title' => 'Yorum Yapıldı', 'icon' => '💬', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'discussion_added' => ['title' => 'Tartışma Eklendi', 'icon' => '💬', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'discussion_updated' => ['title' => 'Tartışma Güncellendi', 'icon' => '✏️', 'bg' => '#ecfeff', 'text' => '#155e75', 'border' => '#a5f3fc'],
            'discussion_deleted' => ['title' => 'Tartışma Silindi', 'icon' => '🗑️', 'bg' => '#fee2e2', 'text' => '#dc2626', 'border' => '#fecaca'],
            'task_created' => ['title' => 'Görev Eklendi', 'icon' => '📋', 'bg' => '#f0fdfa', 'text' => '#115e59', 'border' => '#99f6e4'],
            'task_status_changed' => ['title' => 'Görev Statüsü Değişti', 'icon' => '📋', 'bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa'],
            'file_uploaded' => ['title' => 'Belge Yüklendi', 'icon' => '📁', 'bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'],
            'file_deleted' => ['title' => 'Belge Silindi', 'icon' => '🗑️', 'bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'observer_added' => ['title' => 'Gözlemci Eklendi', 'icon' => '👁️', 'bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],
            'observer_removed' => ['title' => 'Gözlemci Çıkarıldı', 'icon' => '🚫', 'bg' => '#fff1f2', 'text' => '#be123c', 'border' => '#fecdd3'],
            'submitted_for_approval' => ['title' => 'Onaya Sunuldu', 'icon' => '📤', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
            'approval_requested' => ['title' => 'Onay İstendi', 'icon' => '📤', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
            'approval_reverted' => ['title' => 'Onay Geri Alındı', 'icon' => '↩️', 'bg' => '#fff1f2', 'text' => '#9f1239', 'border' => '#fecdd3'],
            'sync' => ['title' => 'Senkronize Edildi', 'icon' => '⚡', 'bg' => '#faf5ff', 'text' => '#6b21a8', 'border' => '#e9d5ff'],
            'draft_shared' => ['title' => 'Taslak Paylaşıldı', 'icon' => '👥', 'bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe'],
            'draft_suggestion_added' => ['title' => 'Taslak Önerisi Eklendi', 'icon' => '💡', 'bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
            'draft_published' => ['title' => 'Taslak Yayına Alındı', 'icon' => '🚀', 'bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#86efac'],
            'draft_external_shared' => ['title' => 'Harici Paylaşıldı', 'icon' => '🌐', 'bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#7dd3fc'],
            'draft_external_revoked' => ['title' => 'Harici Erişim Kesildi', 'icon' => '🚫', 'bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fca5a5'],
            'vendor_added' => ['title' => 'Dış Paydaş Talep Edildi', 'icon' => '🏢', 'bg' => '#f0fdfa', 'text' => '#0f766e', 'border' => '#99f6e4'],
            'vendor_approved' => ['title' => 'Dış Paydaş Onaylandı', 'icon' => '✅', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
            'vendor_rejected' => ['title' => 'Dış Paydaş Reddedildi', 'icon' => '❌', 'bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#f87171'],
            'vendor_revoked' => ['title' => 'Dış Paydaş Çıkarıldı', 'icon' => '🚫', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'vendor_file_uploaded' => ['title' => 'Dış Paydaş Belgesi Yüklendi', 'icon' => '📥', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'vendor_file_approved' => ['title' => 'Dış Paydaş Belgesi Kabul Edildi', 'icon' => '🛡️', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
            'vendor_file_rejected' => ['title' => 'Dış Paydaş Belgesi Reddedildi', 'icon' => '❌', 'bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#f87171'],
            'vendor_file_voided' => ['title' => 'Belge Geçersiz/Dikkate Alma İşaretlendi', 'icon' => '⚠️', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
            'vendor_file_visibility' => ['title' => 'Firma Belge Görünürlüğü Değiştirildi', 'icon' => '👁️', 'bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],
            'auto_approved' => ['title' => 'Doğrudan Devreye Alındı', 'icon' => '⚡', 'bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#86efac'],
            'committee_approval_pending' => ['title' => 'Kurul Oylamasına Sevk Edildi', 'icon' => '⚖️', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
            'committee_voted' => ['title' => 'Kurulda Oy Kullanıldı', 'icon' => '🗳️', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'committee_decision_finalized' => ['title' => 'Kurul Kararı Kesinleşti', 'icon' => '🏁', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
            'member_added' => ['title' => 'Üye Eklendi', 'icon' => '👤', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'member_removed' => ['title' => 'Üye Çıkarıldı', 'icon' => '🚫', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'user_added' => ['title' => 'Kullanıcı Eklendi', 'icon' => '👤', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'user_removed' => ['title' => 'Kullanıcı Çıkarıldı', 'icon' => '🚫', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'user_deleted' => ['title' => 'Kullanıcı Silindi', 'icon' => '🗑️', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'participant_added' => ['title' => 'Katılımcı Eklendi', 'icon' => '👥', 'bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
            'participant_removed' => ['title' => 'Katılımcı Çıkarıldı', 'icon' => '🚫', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
            'task_assigned' => ['title' => 'Personel Atandı', 'icon' => '👤', 'bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],
            'task_unassigned' => ['title' => 'Personel Çıkarıldı', 'icon' => '🚫', 'bg' => '#fff1f2', 'text' => '#be123c', 'border' => '#fecdd3'],
            'role_assigned' => ['title' => 'Rol Atandı', 'icon' => '🛡️', 'bg' => '#faf5ff', 'text' => '#6b21a8', 'border' => '#e9d5ff'],
            'role_removed' => ['title' => 'Rol Kaldırıldı', 'icon' => '🚫', 'bg' => '#fff1f2', 'text' => '#be123c', 'border' => '#fecdd3'],
            'permission_granted' => ['title' => 'Yetki Verildi', 'icon' => '🔑', 'bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
            'permission_revoked' => ['title' => 'Yetki Kaldırıldı', 'icon' => '🚫', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],

            // Kurumsal Vekalet & Devir Sistemi
            'proxy_assigned' => ['title' => 'Vekalet Atandı', 'icon' => '🛡️', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
            'proxy_revoked' => ['title' => 'Vekalet İptal Edildi', 'icon' => '🚫', 'bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca'],
            'proxy_expired' => ['title' => 'Vekalet Süresi Doldu', 'icon' => '⌛', 'bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
            'proxy_scope_updated' => ['title' => 'Vekalet Kapsamı Güncellendi', 'icon' => '✏️', 'bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],

            // Proje İçi Ekip Yetki Matrisi & Devir
            'matrix_delegated' => ['title' => 'Ekip Matris Yetkilendirmesi', 'icon' => '⚡', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
            'member_handover' => ['title' => 'Görev & Rol Devredildi', 'icon' => '🔄', 'bg' => '#f0fdfa', 'text' => '#0f766e', 'border' => '#99f6e4'],
            'manager_transferred' => ['title' => 'Proje Lideri Devredildi', 'icon' => '👑', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
            'visibility_changed' => ['title' => 'Gizlilik Ayarı Değişti', 'icon' => '👁️', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'file_tags_updated' => ['title' => 'Belge Etiketleri Güncellendi', 'icon' => '🏷️', 'bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1'],
            'gatekeeper_reverted' => ['title' => 'İSG Kilidi Geri Alındı', 'icon' => '↩️', 'bg' => '#fff1f2', 'text' => '#9f1239', 'border' => '#fecdd3'],
        ];
    }

    /**
     * Tüm sistem modül / varlık türleri için standart kurumsal Türkçe başlık ve ikon kataloğu (Tek Merkez)
     */
    public static function getCategoryCatalog(): array
    {
        return [
            'Project' => ['title' => 'Proje Ana Kayıt', 'icon' => '📁', 'bg' => '#f0f9ff', 'text' => '#0369a1', 'border' => '#bae6fd'],
            'ProjectPhase' => ['title' => 'Faz & Aşama Yönetimi', 'icon' => '🎯', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
            'ProjectTask' => ['title' => 'Görev & İş Paketi', 'icon' => '📋', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'ProjectTimesheet' => ['title' => 'Zaman & Efor Takibi', 'icon' => '⏱️', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
            'ProjectDraftSuggestion' => ['title' => 'Taslak Proje Önerisi', 'icon' => '💡', 'bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
            'ProjectDraftShare' => ['title' => 'Harici Taslak Paylaşımı', 'icon' => '🌐', 'bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#7dd3fc'],
            'ProjectCategory' => ['title' => 'Proje Kategorisi', 'icon' => '🏷️', 'bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1'],
            'ProjectDiscussion' => ['title' => 'Tartışma & İletişim', 'icon' => '💬', 'bg' => '#f0fdfa', 'text' => '#0f766e', 'border' => '#99f6e4'],
            'ProjectFile' => ['title' => 'Dosya & Doküman', 'icon' => '📄', 'bg' => '#faf5ff', 'text' => '#7e22ce', 'border' => '#e9d5ff'],
            'ProjectRisk' => ['title' => 'Risk Yönetimi', 'icon' => '⚠️', 'bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
            'ProjectRiskMitigation' => ['title' => 'Risk Önlemi & Aksiyon', 'icon' => '🛡️', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
            'ProjectBom' => ['title' => 'BOM & Malzeme Listesi', 'icon' => '📦', 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'],
            'ProjectBudgetItem' => ['title' => 'Bütçe Kalemi & Harcama', 'icon' => '💰', 'bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fef08a'],
            'ProjectBudgetCategory' => ['title' => 'Bütçe Kategorisi', 'icon' => '🏷️', 'bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1'],
            'DoaThresholdRule' => ['title' => 'Bütçe Eşik Kuralı (DOA)', 'icon' => '⚖️', 'bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fef08a'],
            'Committee' => ['title' => 'Kurul & Komite', 'icon' => '👥', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
            'CommitteeMember' => ['title' => 'Kurul Üyeliği', 'icon' => '👤', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'CommitteeVote' => ['title' => 'Kurul Oyu', 'icon' => '🗳️', 'bg' => '#ecfeff', 'text' => '#0e7490', 'border' => '#a5f3fc'],
            'ProjectApprovalRequest' => ['title' => 'Proje Onay Talebi & Snapshot', 'icon' => '📑', 'bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
            'ProjectMeeting' => ['title' => 'Toplantı & Tutanak', 'icon' => '📅', 'bg' => '#f1f5f9', 'text' => '#334155', 'border' => '#cbd5e1'],
            'ProjectStakeholder' => ['title' => 'Proje Paydaşı', 'icon' => '👥', 'bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe'],
            'ProjectStakeholderPermission' => ['title' => 'Ekip Yetki Matrisi', 'icon' => '🔑', 'bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
            'UserDelegation' => ['title' => 'Kurumsal Vekalet', 'icon' => '🛡️', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
            'Bolum' => ['title' => 'Departman & Bölüm', 'icon' => '🏢', 'bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1'],
            'Role' => ['title' => 'Rol & Yetki Matrisi', 'icon' => '🛡️', 'bg' => '#faf5ff', 'text' => '#6b21a8', 'border' => '#e9d5ff'],
            'Permission' => ['title' => 'Kullanıcı İzni', 'icon' => '🔑', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
            'SystemSetting' => ['title' => 'Sistem Ayarları', 'icon' => '⚙️', 'bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#cbd5e1'],
            'NotificationMatrix' => ['title' => 'Bildirim Matrisi', 'icon' => '🔔', 'bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#bae6fd'],
            'NotificationTemplate' => ['title' => 'Bildirim Şablonu', 'icon' => '✉️', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'User' => ['title' => 'Kullanıcı & Personel', 'icon' => '👤', 'bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
        ];
    }

    public static function getActionMeta(?string $action): array
    {
        $actionLower = strtolower(trim((string)$action));
        $catalog = self::getActionCatalog();

        $meta = $catalog[$actionLower] ?? null;

        if (!$meta) {
            // Akıllı Türkçe Çeviri Fallback (Asla ham İngilizce bırakılmaz)
            $trDictionary = [
                'member_added' => 'Üye Eklendi',
                'member_removed' => 'Üye Çıkarıldı',
                'user_added' => 'Kullanıcı Eklendi',
                'user_removed' => 'Kullanıcı Çıkarıldı',
                'user_deleted' => 'Kullanıcı Silindi',
                'participant_added' => 'Katılımcı Eklendi',
                'participant_removed' => 'Katılımcı Çıkarıldı',
                'stakeholder_added' => 'Paydaş Eklendi',
                'stakeholder_removed' => 'Paydaş Çıkarıldı',
                'invite_sent' => 'Davet Gönderildi',
                'invite_revoked' => 'Davet Geri Alındı',
            ];

            if (isset($trDictionary[$actionLower])) {
                $friendlyLabel = $trDictionary[$actionLower];
            } else {
                $friendlyLabel = str_replace(
                    ['added', 'removed', 'deleted', 'created', 'updated', 'member', 'user', 'participant', 'stakeholder'],
                    ['Eklendi', 'Çıkarıldı', 'Silindi', 'Oluşturuldu', 'Güncellendi', 'Üye', 'Kullanıcı', 'Katılımcı', 'Paydaş'],
                    $actionLower
                );
                $friendlyLabel = ucwords(str_replace('_', ' ', $friendlyLabel));
            }

            $icon = '📌';
            $bg = '#f1f5f9';
            $text = '#475569';
            $border = '#cbd5e1';

            if (str_contains($actionLower, 'remove') || str_contains($actionLower, 'delete') || str_contains($actionLower, 'çıkar') || str_contains($actionLower, 'sil')) {
                $icon = '🚫';
                $bg = '#fef2f2';
                $text = '#b91c1c';
                $border = '#fecaca';
            } elseif (str_contains($actionLower, 'add') || str_contains($actionLower, 'create') || str_contains($actionLower, 'ekle')) {
                $icon = '👤';
                $bg = '#eff6ff';
                $text = '#1d4ed8';
                $border = '#bfdbfe';
            }

            $meta = [
                'title' => $friendlyLabel ?: 'İşlem',
                'icon' => $icon,
                'bg' => $bg,
                'text' => $text,
                'border' => $border
            ];
        }

        $meta['label'] = $meta['title'];
        $meta['color'] = $meta['text'];

        return $meta;
    }

    public static function getCategoryMeta(?string $entityType): array
    {
        $catalog = self::getCategoryCatalog();

        $meta = ($entityType && isset($catalog[$entityType])) ? $catalog[$entityType] : null;

        if (!$meta) {
            $title = $entityType ? \Illuminate\Support\Str::headline($entityType) : 'Genel Sistem';
            $meta = [
                'title' => $title,
                'icon' => '🏷️',
                'bg' => '#f8fafc',
                'text' => '#475569',
                'border' => '#e2e8f0'
            ];
        }

        $meta['label'] = $meta['title'];
        $meta['color'] = $meta['text'];

        return $meta;
    }

    public function getActionMetaAttribute(): array
    {
        return self::getActionMeta($this->action);
    }

    public function getCategoryMetaAttribute(): array
    {
        return self::getCategoryMeta($this->entity_type);
    }
}
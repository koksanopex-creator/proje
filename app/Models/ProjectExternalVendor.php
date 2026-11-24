<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectExternalVendor extends Model
{
    use SoftDeletes;

    protected $table = 'project_external_vendors';

    protected $fillable = [
        'project_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'service_scope',
        'allowed_categories',
        'access_token',
        'passkey',
        'credentials_sent_at',
        'credentials_sent_by_user_id',
        'status',
        'added_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
        'rejected_by_user_id',
        'rejected_at',
        'revoked_by_user_id',
        'revoked_at',
        'revocation_reason',
        'expires_at',
        'last_accessed_at',
        'access_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'revoked_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'access_count' => 'integer',
            'allowed_categories' => 'array',
            'credentials_sent_at' => 'datetime',
        ];
    }

    public static function defaultCategories(): array
    {
        return [
            'Kaynak / İmalat Muayene Raporu',
            'Test & Devreye Alma Raporu',
            'Malzeme / Kalite Sertifikası',
            'İmalat / Montaj Çizimi (As-Built)',
            'İrsaliye & Mal Kabul Tutanağı',
            'Diğer Teknik Doküman',
        ];
    }

    public function getCategoriesListAttribute(): array
    {
        if (!empty($this->allowed_categories) && is_array($this->allowed_categories)) {
            $filtered = array_values(array_filter(array_map('trim', $this->allowed_categories)));
            if (!empty($filtered)) {
                return $filtered;
            }
        }

        return self::defaultCategories();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function credentialsSentBy()
    {
        return $this->belongsTo(User::class, 'credentials_sent_by_user_id');
    }

    public function getMaskedPasskeyAttribute(): string
    {
        if (strlen($this->passkey) <= 4) {
            return '••••';
        }
        return substr($this->passkey, 0, 4) . '••••-•••';
    }

    public function files()
    {
        return $this->hasMany(ProjectVendorFile::class, 'vendor_id');
    }

    public function inboundFiles()
    {
        return $this->hasMany(ProjectVendorFile::class, 'vendor_id')->where('direction', 'inbound_from_vendor');
    }

    public function outboundFiles()
    {
        return $this->hasMany(ProjectVendorFile::class, 'vendor_id')->where('direction', 'outbound_to_vendor');
    }

    /**
     * Dış firmanın portala giriş izni var mı?
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    /**
     * Proje tamamlandı veya kapandı mı?
     */
    public function isProjectClosed(): bool
    {
        if (!$this->project) return false;
        return in_array($this->project->status, ['completed', 'cancelled', 'archived']);
    }

    /**
     * Aktif erişim hakkı var mı?
     */
    public function canAccessPortal(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        if ($this->isProjectClosed()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public static function generateSecureToken(): string
    {
        return Str::random(54);
    }

    public static function generatePasskey(): string
    {
        return 'KKS-' . strtoupper(Str::random(4)) . '-' . rand(100, 999);
    }

    public function getPortalUrlAttribute(): string
    {
        return route('vendor_portal.show', ['token' => $this->access_token]);
    }
}

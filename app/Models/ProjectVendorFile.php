<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectVendorFile extends Model
{
    use SoftDeletes;

    protected $table = 'project_vendor_files';

    protected $fillable = [
        'project_id',
        'vendor_id',
        'original_name',
        'storage_path',
        'file_size',
        'mime_type',
        'direction',
        'status',
        'is_visible_to_vendor',
        'is_void',
        'void_reason',
        'voided_by_type',
        'voided_by_id',
        'voided_at',
        'uploaded_by_vendor_id',
        'uploaded_by_user_id',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_note',
        'category',
        'notes',
        'upload_batch_id',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_vendor' => 'boolean',
            'is_void' => 'boolean',
            'file_size' => 'integer',
            'reviewed_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor()
    {
        return $this->belongsTo(ProjectExternalVendor::class, 'vendor_id');
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }
        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx', 'csv' => '📊',
            'jpg', 'jpeg', 'png', 'webp' => '🖼️',
            'dwg', 'dxf', 'cad', 'step', 'stp' => '📐',
            'zip', 'rar', '7z' => '📦',
            default => '📎',
        };
    }

    /**
     * Dosyayı geçersiz / dikkate alma olarak işaretle
     */
    public function markAsVoid(string $reason, string $type = 'internal_user', $actorId = null): void
    {
        $this->update([
            'is_void' => true,
            'void_reason' => trim($reason),
            'voided_by_type' => $type,
            'voided_by_id' => $actorId,
            'voided_at' => now(),
        ]);
    }
}

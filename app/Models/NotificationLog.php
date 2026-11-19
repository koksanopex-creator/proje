<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_logs';

    protected $fillable = [
        'channel',
        'event_key',
        'event_title',
        'user_id',
        'recipient_name',
        'recipient_email',
        'subject',
        'body',
        'action_url',
        'status',
        'error_message',
        'sent_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function scopeChannel($query, $channel)
    {
        if ($channel) {
            return $query->where('channel', $channel);
        }
        return $query;
    }

    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeSearch($query, $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('recipient_name', 'like', "%{$term}%")
              ->orWhere('recipient_email', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%")
              ->orWhere('event_title', 'like', "%{$term}%")
              ->orWhere('event_key', 'like', "%{$term}%")
              ->orWhere('body', 'like', "%{$term}%");
        });
    }

    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'mail' => 'E-Posta',
            'bell' => 'Zil Bildirimi',
            default => ucfirst((string)$this->channel),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'sent' => ['label' => 'İletildi / Başarılı', 'class' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'],
            'failed' => ['label' => 'Başarısız', 'class' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20'],
            'pending' => ['label' => 'Beklemede', 'class' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20'],
            default => ['label' => ucfirst((string)$this->status), 'class' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20'],
        };
    }
}

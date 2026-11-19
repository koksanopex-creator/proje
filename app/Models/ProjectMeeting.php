<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMeeting extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'meeting_date',
        'location',
        'agenda',
        'minutes',
        'status',
        'organizer_id',
        'postpone_reason',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'datetime',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function organizer() { return $this->belongsTo(User::class, 'organizer_id'); }
    public function participants() { return $this->hasMany(ProjectMeetingParticipant::class, 'meeting_id'); }
    public function actions() { return $this->hasMany(ProjectMeetingAction::class, 'meeting_id'); }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
            'in_progress' => 'Devam Ediyor',
            'postponed' => 'Ertelendi',
            default => 'Planlandı',
        };
    }

    public function getStatusBadgeStyleAttribute(): string
    {
        return match($this->status) {
            'completed' => 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;',
            'cancelled' => 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;',
            'in_progress' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
            'postponed' => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
            default => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
        };
    }

    public function scopeForUser($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isYonetim() || $user->hasPermission('meeting.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('organizer_id', $user->id)
              ->orWhereHas('participants', function ($pq) use ($user) {
                  $pq->where('user_id', $user->id);
              })
              ->orWhereHas('project', function ($pq) use ($user) {
                  $pq->where('manager_id', $user->id)
                    ->orWhere('sponsor_id', $user->id)
                    ->orWhereHas('stakeholders', function ($sq) use ($user) {
                        $sq->where('user_id', $user->id);
                    });
              });
        });
    }

    /**
     * Gündem maddelerini numaralı ve ek bilgileri (tarih, sorumlu, notlar) ayrıştırılmış nesne listesi olarak döndürür.
     */
    public function getFormattedAgendaItemsAttribute(): array
    {
        if (empty($this->agenda)) {
            return [];
        }

        // 1. JSON kontrolü
        $trimmed = trim($this->agenda);
        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $items = [];
                foreach ($decoded as $idx => $row) {
                    $items[] = [
                        'number' => $idx + 1,
                        'title' => $row['title'] ?? ($row['text'] ?? ($row['item'] ?? '')),
                        'responsible' => $row['responsible'] ?? ($row['person'] ?? null),
                        'date' => $row['date'] ?? ($row['due_date'] ?? null),
                        'notes' => $row['notes'] ?? ($row['extra'] ?? null),
                    ];
                }
                return $items;
            }
        }

        // 2. Çok satırlı düz metin ayrıştırma
        $lines = preg_split('/(\r\n|\n|\r)/', $this->agenda);
        $items = [];
        $counter = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Satır başındaki "1.", "1-", "1)", "-", "*" temizle
            $cleanLine = preg_replace('/^(\d+[\.\-\)]|\-|\*)\s*/u', '', $line);

            $responsible = null;
            $date = null;
            $notes = null;

            // Tarih kontrolü (dd.mm.yyyy veya yyyy-mm-dd veya dd/mm/yyyy)
            if (preg_match('/(tarih|termin)[\s\:\-]+([0-9]{1,2}[\.\-\/][0-9]{1,2}[\.\-\/][0-9]{2,4}|[0-9]{4}[\.\-\/][0-9]{1,2}[\.\-\/][0-9]{1,2})/ui', $cleanLine, $dateMatch)) {
                $date = $dateMatch[2];
                $cleanLine = str_replace($dateMatch[0], '', $cleanLine);
            }

            // Sorumlu kontrolü (sorumlu: Ahmet Yılmaz)
            if (preg_match('/(sorumlu|atanan|ilgili|yetkili)[\s\:\-]+([^\|,\(\)]+)/ui', $cleanLine, $respMatch)) {
                $responsible = trim($respMatch[2]);
                $cleanLine = str_replace($respMatch[0], '', $cleanLine);
            }

            // Ek not kontrolü (not: XYZ veya ek bilgi: XYZ)
            if (preg_match('/(not|ek bilgi|açıklama)[\s\:\-]+([^\|,\(\)]+)/ui', $cleanLine, $noteMatch)) {
                $notes = trim($noteMatch[2]);
                $cleanLine = str_replace($noteMatch[0], '', $cleanLine);
            }

            // Artakalan pipe veya parantezleri temizle
            $title = trim(preg_replace('/[\(\)\|\-]+$/u', '', preg_replace('/^[\(\)\|\-]+/u', '', $cleanLine)));
            if ($title === '') {
                $title = $line;
            }

            $items[] = [
                'number' => $counter++,
                'title' => $title,
                'responsible' => $responsible,
                'date' => $date,
                'notes' => $notes,
                'raw' => $line,
            ];
        }

        return $items;
    }
}
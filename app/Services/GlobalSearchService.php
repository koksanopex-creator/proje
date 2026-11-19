<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GlobalSearchService
{
    /**
     * Tüm sistem genelinde yetki kontrollü çoklu varlık araması yapar.
     */
    public function search(string $query, User $user, int $limit = 6): array
    {
        $cleanQuery = trim($query);
        if (mb_strlen($cleanQuery) < 2) {
            return [
                'query' => $cleanQuery,
                'projects' => [],
                'files' => [],
                'users' => [],
                'total_count' => 0,
            ];
        }

        $isTagSearch = str_starts_with($cleanQuery, '#');
        $tagTerm = ltrim($cleanQuery, '#');

        $projects = $this->searchProjects($cleanQuery, $user, $limit);
        $files = $this->searchFiles($cleanQuery, $tagTerm, $isTagSearch, $user, $limit);
        $users = $this->searchUsers($cleanQuery, $user, $limit);

        return [
            'query' => $cleanQuery,
            'is_tag_search' => $isTagSearch,
            'projects' => $projects,
            'files' => $files,
            'users' => $users,
            'total_count' => count($projects) + count($files) + count($users),
        ];
    }

    /**
     * Kullanıcının erişebildiği projeler içerisinde arama yapar.
     */
    protected function searchProjects(string $query, User $user, int $limit): array
    {
        $projectQuery = Project::query()->where('is_template', false);

        // Güvenlik & Yetki Filtresi
        $this->applyProjectAccessScope($projectQuery, $user);

        // Metin Araması
        $projectQuery->where(function (Builder $q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('code', 'like', "%{$query}%")
              ->orWhere('category', 'like', "%{$query}%");
        });

        return $projectQuery->with(['manager', 'bolum'])
            ->latest('updated_at')
            ->take($limit)
            ->get()
            ->map(function (Project $p) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'title' => $p->title,
                    'category' => $p->category,
                    'status' => $p->status,
                    'status_label' => $p->status_label ?? $p->status,
                    'manager_name' => $p->manager?->name ?? 'Belirtilmedi',
                    'department_name' => $p->bolum?->name ?? 'Genel',
                    'progress' => $p->overall_progress ?? 0,
                    'url' => route('projects.show', $p->id),
                ];
            })
            ->toArray();
    }

    /**
     * SADECE kullanıcının erişebildiği projelerin dosyalarında ve etiketlerinde arama yapar.
     */
    protected function searchFiles(string $query, string $tagTerm, bool $isTagSearch, User $user, int $limit): array
    {
        $accessibleProjectQuery = Project::query()->where('is_template', false);
        $this->applyProjectAccessScope($accessibleProjectQuery, $user);
        $accessibleProjectIds = $accessibleProjectQuery->pluck('id');

        if ($accessibleProjectIds->isEmpty()) {
            return [];
        }

        $fileQuery = ProjectFile::query()
            ->whereIn('project_id', $accessibleProjectIds)
            ->with(['project']);

        if ($isTagSearch) {
            // Doğrudan etiket araması (#etiket)
            $fileQuery->where(function (Builder $q) use ($tagTerm) {
                $q->where('tags', 'like', "%\"{$tagTerm}\"%")
                  ->orWhere('tags', 'like', "%{$tagTerm}%");
            });
        } else {
            // Genel dosya adı veya etiket araması
            $fileQuery->where(function (Builder $q) use ($query) {
                $q->where('original_name', 'like', "%{$query}%")
                  ->orWhere('tags', 'like', "%{$query}%");
            });
        }

        return $fileQuery->latest('created_at')
            ->take($limit)
            ->get()
            ->map(function (ProjectFile $file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'project_id' => $file->project_id,
                    'project_code' => $file->project?->code ?? '',
                    'project_title' => $file->project?->title ?? '',
                    'tags' => $file->tags_array,
                    'category' => $file->category,
                    'file_size' => $this->formatBytes($file->file_size),
                    'mime_type' => $file->mime_type,
                    'download_url' => route('projects.download_file', [$file->project_id, $file->id]),
                    'project_url' => route('projects.show', $file->project_id) . '#files',
                ];
            })
            ->toArray();
    }

    /**
     * Kullanıcılar/Ekip üyeleri arasında arama yapar.
     */
    protected function searchUsers(string $query, User $user, int $limit): array
    {
        // Harici kullanıcılar sistem kullanıcılarını listeleyemez
        if ($user->isExternal()) {
            return [];
        }

        return User::query()
            ->where('is_active', true)
            ->where(function (Builder $q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('unvan', 'like', "%{$query}%")
                  ->orWhereHas('bolum', function (Builder $bq) use ($query) {
                      $bq->where('name', 'like', "%{$query}%");
                  });
            })
            ->with('bolum')
            ->take($limit)
            ->get()
            ->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'job_title' => $u->unvan ?? 'Çalışan',
                    'department' => $u->bolum?->name ?? 'Belirtilmedi',
                    'avatar' => $u->avatar ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Güvenlik filtresi: Proje erişim yetki kurallarını sorguya enjekte eder.
     */
    protected function applyProjectAccessScope(Builder $query, User $user): void
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->hasPermission('proje.view_all')) {
            return;
        }

        if ($user->isExternal()) {
            $query->whereHas('stakeholders', function (Builder $sq) use ($user) {
                $sq->where('user_id', $user->id);
            });
            return;
        }

        $query->where(function (Builder $q) use ($user) {
            if ($user->hasPermission('proje.view_department') && $user->bolum_id) {
                $q->where('bolum_id', $user->bolum_id);
            }

            $q->orWhere('manager_id', $user->id)
              ->orWhere('sponsor_id', $user->id)
              ->orWhere('created_by', $user->id);

            $q->orWhereHas('stakeholders', function (Builder $sq) use ($user) {
                $sq->where('user_id', $user->id);
            });
        });
    }

    /**
     * Bayt boyutunu okunabilir formata dönüştürür.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . ($units[$i] ?? 'B');
    }
}

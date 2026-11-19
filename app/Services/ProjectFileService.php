<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFileService
{
    /**
     * Projeye yeni dosya yükler ve etiketleri normalize eder.
     */
    public function uploadFile(Project $project, UploadedFile $uploadedFile, array $attributes, User $user): ProjectFile
    {
        $userName = Str::slug($user->name, '_');
        $dateStr = now()->format('d.m.Y_H.i');
        $randomStr = Str::random(2);
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileName = "{$dateStr}_{$randomStr}.{$extension}";
        $storageDir = "proje_dosyalari/{$userName}/{$project->id}";
        $storagePath = $uploadedFile->storeAs($storageDir, $fileName, 'public');

        $tags = $this->parseTags($attributes['tags'] ?? []);

        $projectFile = $project->files()->create([
            'original_name' => $uploadedFile->getClientOriginalName(),
            'storage_path' => $storagePath,
            'mime_type' => $uploadedFile->getMimeType() ?: 'application/octet-stream',
            'file_size' => $uploadedFile->getSize() ?: 0,
            'category' => $attributes['category'] ?? 'kapsam_belgesi',
            'tags' => $tags,
            'uploaded_by' => $user->id,
        ]);

        $tagsInfo = !empty($tags) ? ' (Etiketler: #' . implode(', #', $tags) . ')' : '';
        AuditLogService::log(
            'file_uploaded',
            "Projeye yeni doküman yüklendi: **{$projectFile->original_name}**{$tagsInfo}",
            'Project',
            $project->id,
            $project->id
        );

        return $projectFile;
    }

    /**
     * Mevcut bir dosyanın etiketlerini günceller.
     */
    public function updateTags(Project $project, ProjectFile $file, $tagsInput, User $user): ProjectFile
    {
        if ($file->project_id !== $project->id) {
            abort(404, 'Dosya projeye ait değil.');
        }

        $newTags = $this->parseTags($tagsInput);
        $file->update(['tags' => $newTags]);

        $tagsText = !empty($newTags) ? '#' . implode(', #', $newTags) : 'Yok';
        AuditLogService::log(
            'file_tags_updated',
            "**{$file->original_name}** dosyasının etiketleri güncellendi: {$tagsText}",
            'ProjectFile',
            $file->id,
            $project->id
        );

        return $file->fresh();
    }

    /**
     * Proje dosyasını güvenli bir şekilde siler.
     */
    public function deleteFile(Project $project, ProjectFile $file, User $user): void
    {
        if ($file->project_id !== $project->id) {
            abort(404, 'Dosya projeye ait değil.');
        }

        $fileName = $file->original_name;
        $file->delete();

        AuditLogService::log(
            'file_deleted',
            "Proje dokümanı silindi: **{$fileName}**",
            'Project',
            $project->id,
            $project->id
        );
    }

    /**
     * Dosya indirme isteğini doğrular ve akışını sağlar.
     */
    public function downloadFile(Project $project, ProjectFile $file, User $user)
    {
        if ($file->project_id !== $project->id) {
            abort(404, 'Dosya projeye ait değil.');
        }

        if (!$user->canAccessProject($project)) {
            abort(403, 'Bu projenin dokümanlarını indirme yetkiniz bulunmamaktadır.');
        }

        if (!Storage::disk('public')->exists($file->storage_path)) {
            abort(404, 'Dosya sunucu diskinde bulunamadı.');
        }

        return Storage::disk('public')->download($file->storage_path, $file->original_name);
    }

    /**
     * Belirtilen proje veya genel sistem için popüler etiketleri ve kullanım sayılarını döner.
     */
    public function getPopularTags(?int $projectId = null, ?User $user = null): array
    {
        $query = ProjectFile::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        } elseif ($user && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $accessibleProjectIds = Project::query()
                ->where(function ($q) use ($user) {
                    if ($user->hasPermission('proje.view_department') && $user->bolum_id) {
                        $q->where('bolum_id', $user->bolum_id);
                    }
                    $q->orWhere('manager_id', $user->id)
                      ->orWhere('sponsor_id', $user->id)
                      ->orWhere('created_by', $user->id)
                      ->orWhereHas('stakeholders', function ($sq) use ($user) {
                          $sq->where('user_id', $user->id);
                      });
                })
                ->pluck('id');

            $query->whereIn('project_id', $accessibleProjectIds);
        }

        $files = $query->whereNotNull('tags')->get(['tags']);
        $tagCounts = [];

        foreach ($files as $file) {
            $tags = $file->tags_array;
            foreach ($tags as $tag) {
                $cleanTag = trim($tag);
                if ($cleanTag !== '') {
                    $tagCounts[$cleanTag] = ($tagCounts[$cleanTag] ?? 0) + 1;
                }
            }
        }

        arsort($tagCounts);
        return $tagCounts;
    }

    /**
     * String veya dizi etiketleri temizleyip normalize diziye dönüştürür.
     */
    public function parseTags($tagsInput): array
    {
        if (is_string($tagsInput)) {
            $tagsArray = preg_split('/[,;\s]+/', $tagsInput, -1, PREG_SPLIT_NO_EMPTY);
        } elseif (is_array($tagsInput)) {
            $tagsArray = $tagsInput;
        } else {
            return [];
        }

        $cleanTags = [];
        foreach ($tagsArray as $tag) {
            $tag = trim($tag);
            $tag = ltrim($tag, '#');
            $tag = preg_replace('/[^\p{L}\p{N}_\-]/u', '', $tag);
            if ($tag !== '' && mb_strlen($tag) <= 50) {
                $cleanTags[] = $tag;
            }
        }

        return array_values(array_unique($cleanTags));
    }
}

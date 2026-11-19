<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRisk extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'category',
        'probability', 'impact', 'risk_score', 'severity', 'status', 'owner_id'
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function mitigations() { return $this->hasMany(ProjectRiskMitigation::class, 'risk_id'); }

    public function getCategoryLabelAttribute(): string
    {
        return match(strtolower(trim((string)$this->category))) {
            'isg'               => 'İSG & Çevre Güvenliği',
            'tedarik'           => 'Tedarik & Lojistik',
            'teknik'            => 'Teknik & Mühendislik',
            'finansal'          => 'Finansal & Maliyet',
            'yasal'             => 'Yasal & İzin Süreçleri',
            'operasyonel'       => 'Operasyonel / Üretim',
            'insan_kaynaklari'  => 'İnsan Kaynakları',
            default             => $this->category ? ucfirst(str_replace('_', ' ', $this->category)) : '-'
        };
    }
}

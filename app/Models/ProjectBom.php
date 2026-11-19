<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectBom extends Model
{
    protected $fillable = [
        'project_id', 'item_code', 'item_name', 'category', 'quantity',
        'unit', 'unit_price', 'total_price', 'currency', 'supplier', 'status', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Sistem Standart BOM Malzeme Kategorileri Kataloğu
     */
    public static function getCategoryCatalog(): array
    {
        return [
            'hammadde' => [
                'name' => 'Hammadde & Reçine',
                'color' => '#065f46',
                'bg' => '#ecfdf5',
                'border' => '#a7f3d0',
                'icon' => '🧪',
            ],
            'mekanik' => [
                'name' => 'Mekanik & Çelik',
                'color' => '#0369a1',
                'bg' => '#f0f9ff',
                'border' => '#bae6fd',
                'icon' => '⚙️',
            ],
            'elektrik' => [
                'name' => 'Elektrik & Altyapı',
                'color' => '#854d0e',
                'bg' => '#fefce8',
                'border' => '#fef08a',
                'icon' => '⚡',
            ],
            'otomasyon' => [
                'name' => 'Otomasyon & SCADA',
                'color' => '#5b21b6',
                'bg' => '#f5f3ff',
                'border' => '#ddd6fe',
                'icon' => '🎛️',
            ],
            'insaat_malzeme' => [
                'name' => 'İnşaat & Tesis Altyapı',
                'color' => '#9a3412',
                'bg' => '#fff7ed',
                'border' => '#fed7aa',
                'icon' => '🏗️',
            ],
            'insaat' => [
                'name' => 'İnşaat & Tesis Altyapı',
                'color' => '#9a3412',
                'bg' => '#fff7ed',
                'border' => '#fed7aa',
                'icon' => '🏗️',
            ],
            'ekipman' => [
                'name' => 'Endüstriyel Ekipman',
                'color' => '#1e40af',
                'bg' => '#eff6ff',
                'border' => '#bfdbfe',
                'icon' => '🚜',
            ],
            'makine' => [
                'name' => 'Makine İmalat & Montaj',
                'color' => '#1e40af',
                'bg' => '#eff6ff',
                'border' => '#bfdbfe',
                'icon' => '🏭',
            ],
            'ambalaj' => [
                'name' => 'Ambalaj & Paketleme',
                'color' => '#155e75',
                'bg' => '#ecfeff',
                'border' => '#a5f3fc',
                'icon' => '📦',
            ],
            'yedek_parca' => [
                'name' => 'Yedek Parça & Bakım',
                'color' => '#334155',
                'bg' => '#f8fafc',
                'border' => '#cbd5e1',
                'icon' => '🔧',
            ],
            'sarf_malzeme' => [
                'name' => 'Sarf Malzeme',
                'color' => '#374151',
                'bg' => '#f9fafb',
                'border' => '#e5e7eb',
                'icon' => '🧰',
            ],
            'hizmet_iscilik' => [
                'name' => 'İşçilik & Montaj Hizmeti',
                'color' => '#92400e',
                'bg' => '#fef3c7',
                'border' => '#fde68a',
                'icon' => '👷',
            ],
            'hizmet' => [
                'name' => 'Montaj & Hizmet',
                'color' => '#92400e',
                'bg' => '#fef3c7',
                'border' => '#fde68a',
                'icon' => '👷',
            ],
            'boru_tesisat' => [
                'name' => 'Boru & Tesisat Hatları',
                'color' => '#115e59',
                'bg' => '#f0fdfa',
                'border' => '#99f6e4',
                'icon' => '🚰',
            ],
            'lpg_hatti' => [
                'name' => 'LPG Boru Hattı',
                'color' => '#9a3412',
                'bg' => '#fff7ed',
                'border' => '#fed7aa',
                'icon' => '🔥',
            ],
            'ges_enerji' => [
                'name' => 'GES Enerji Santrali',
                'color' => '#166534',
                'bg' => '#f0fdf4',
                'border' => '#bbf7d0',
                'icon' => '☀️',
            ],
            'sap_bt' => [
                'name' => 'SAP & BT Dijital',
                'color' => '#0369a1',
                'bg' => '#f0f9ff',
                'border' => '#bae6fd',
                'icon' => '💻',
            ],
            'isg_cevre' => [
                'name' => 'İSG & Çevre',
                'color' => '#991b1b',
                'bg' => '#fef2f2',
                'border' => '#fecaca',
                'icon' => '🛡️',
            ],
        ];
    }

    /**
     * Kategori Etiket Standart Bilgisi (İsim, Renk, Arka Plan, İkon)
     */
    public function getCategoryMeta(): array
    {
        $slug = trim((string)$this->category);
        if (empty($slug)) {
            return [
                'name' => 'Genel Malzeme',
                'color' => '#475569',
                'bg' => '#f1f5f9',
                'border' => '#e2e8f0',
                'icon' => '📦',
            ];
        }

        $catalog = self::getCategoryCatalog();
        if (isset($catalog[$slug])) {
            return $catalog[$slug];
        }

        // ProjectCategory modelinden dinamik kontrol
        $projCat = \App\Models\ProjectCategory::where('slug', $slug)->first();
        if ($projCat) {
            $hex = $projCat->color ?: '#0284c7';
            return [
                'name' => $projCat->name,
                'color' => $hex,
                'bg' => '#f8fafc',
                'border' => $hex,
                'icon' => '🏷️',
            ];
        }

        // Bilinmeyen slug gelirse temiz formatla
        $formattedName = Str::title(str_replace(['_', '-'], ' ', $slug));
        return [
            'name' => $formattedName,
            'color' => '#334155',
            'bg' => '#f8fafc',
            'border' => '#cbd5e1',
            'icon' => '🏷️',
        ];
    }
}

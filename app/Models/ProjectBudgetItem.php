<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBudgetItem extends Model
{
    protected $fillable = [
        'project_id', 'title', 'budget_type', 'category', 'planned_amount',
        'committed_amount', 'actual_spent', 'currency', 'invoice_date', 'invoice_no'
    ];

    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
            'committed_amount' => 'decimal:2',
            'actual_spent' => 'decimal:2',
            'invoice_date' => 'date',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
}

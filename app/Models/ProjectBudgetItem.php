<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBudgetItem extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'budget_type',
        'category',
        'sub_category',
        'category_id',
        'sub_category_id',
        'planned_amount',
        'committed_amount',
        'actual_spent',
        'currency',
        'invoice_date',
        'invoice_no'
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectBudgetCategory::class, 'category_id');
    }

    public function budgetSubCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectBudgetCategory::class, 'sub_category_id');
    }
}

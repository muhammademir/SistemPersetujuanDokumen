<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    public const DOCUMENT_TYPES = ['SLF', 'AMDAL', 'IMB', 'UKL-UPL', 'SIUP'];
    protected $fillable = [
    'code', 'applicant_id', 'assigned_reviewer_id',
    'title', 'description', 'document_type', 'status',
    'revision_count', 'submitted_at', 'decided_at',
];

protected function casts(): array
{
    return [
        'status'       => ApplicationStatus::class,
        'submitted_at' => 'datetime',
        'decided_at'   => 'datetime',
    ];
}

public function applicant(): BelongsTo
{
    return $this->belongsTo(User::class, 'applicant_id');
}

public function reviewer(): BelongsTo
{
    return $this->belongsTo(User::class, 'assigned_reviewer_id');
}

public function documents(): HasMany
{
    return $this->hasMany(ApplicationDocument::class);
}

public function reviews(): HasMany
{
    return $this->hasMany(ApplicationReview::class)->latest('reviewed_at');
}

public function statusLogs(): HasMany
{
    return $this->hasMany(ApplicationStatusLog::class)->latest('created_at');
}

// Query scope untuk filter
public function scopeStatus($query, ?string $status)
{
    return $query->when($status, fn ($q) => $q->where('status', $status));
}

public function scopeSearch($query, ?string $term)
{
    return $query->when($term, fn ($q) => $q->where('title', 'ilike', "%{$term}%"));
}
}

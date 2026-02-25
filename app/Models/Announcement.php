<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    public const TARGET_ALL = 'all';
    public const TARGET_SELECTED = 'selected';

    protected $fillable = [
        'title',
        'body',
        'target_type',
        'target_company_ids',
        'created_by',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'target_company_ids' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where(function ($q) use ($companyId) {
            $q->where('target_type', self::TARGET_ALL)
                ->orWhereJsonContains('target_company_ids', $companyId);
        });
    }
}

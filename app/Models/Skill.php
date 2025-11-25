<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'proficiency_level',
        'is_active',
        'description',
        'attachments',
        'tags',
        'notes',
        'archived_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'proficiency_level' => 'integer',
        'attachments' => 'array',
        'tags' => 'array',
        'archived_at' => 'datetime',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(SkillActivity::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeMinProficiency(Builder $query, int $level): Builder
    {
        return $query->where('proficiency_level', '>=', $level);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}

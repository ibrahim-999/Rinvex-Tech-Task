<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillActivity extends Model
{
    protected $fillable = [
        'skill_id',
        'action',
        'payload',
        'user_id',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

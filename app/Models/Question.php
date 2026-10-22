<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;


class Question extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'question_text',
        'topic_id',
        'options',
        'correct_index',
        'slug',

        // ---- Scheduling ----
        'published_at',       // when the admin saved/scheduled it (null = draft)
        'available_from',     // null = available immediately once published
        'available_until',    // null = available indefinitely
        'duration_minutes',   // null = no time limit per attempt
        'closed_at',          // manual close timestamp (safety valve)
    ];

    protected $casts = [
        'options'          => 'array',
        'published_at'     => 'datetime',
        'available_from'   => 'datetime',
        'available_until'  => 'datetime',
        'closed_at'        => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    /* ============================================================
     |  Scheduling helpers
     ============================================================ */

    /**
     * Draft = created but never published/scheduled.
     */
    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    /**
     * Currently open to takers?
     */
    public function isAvailable(): bool
    {
        if ($this->isDraft() || $this->closed_at !== null) {
            return false;
        }

        $now = now();

        // Not started yet
        if ($this->available_from !== null && $now->lt($this->available_from)) {
            return false;
        }

        // Already ended
        if ($this->available_until !== null && $now->gte($this->available_until)) {
            return false;
        }

        return true;
    }

    /**
     * Scheduled but not yet open.
     */
    public function isScheduled(): bool
    {
        return ! $this->isDraft()
            && $this->closed_at === null
            && $this->available_from !== null
            && now()->lt($this->available_from);
    }

    /**
     * Human-friendly state for the admin dashboard.
     * Returns: 'draft' | 'scheduled' | 'active' | 'closed' | 'expired'
     */
    public function status(): string
    {
        if ($this->isDraft()) {
            return 'draft';
        }

        if ($this->closed_at !== null) {
            return 'closed';
        }

        if ($this->available_from !== null && now()->lt($this->available_from)) {
            return 'scheduled';
        }

        if ($this->available_until !== null && now()->gte($this->available_until)) {
            return 'expired';
        }

        return 'active';
    }

    /* ============================================================
     |  Query scopes
     ============================================================ */

    public function scopeDraft(Builder $q): Builder
    {
        return $q->whereNull('published_at');
    }

    public function scopeAvailable(Builder $q): Builder
    {
        $now = now();

        return $q->whereNotNull('published_at')
                 ->whereNull('closed_at')
                 ->where(function ($qq) use ($now) {
                     $qq->whereNull('available_from')
                        ->orWhere('available_from', '<=', $now);
                 })
                 ->where(function ($qq) use ($now) {
                     $qq->whereNull('available_until')
                        ->orWhere('available_until', '>', $now);
                 });
    }

    public function scopeScheduled(Builder $q): Builder
    {
        $now = now();

        return $q->whereNotNull('published_at')
                 ->whereNull('closed_at')
                 ->whereNotNull('available_from')
                 ->where('available_from', '>', $now);
    }
}
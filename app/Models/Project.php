<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'slug',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'owner_id',
        'lead_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $project->slug = Str::slug($project->name);
            $project->status = 'active';
            $project->priority = 'medium';
            // Generate key if not set (you might want to customize this format)
            if (!$project->key) {
                $count = static::count() + 1;
                $project->key = 'TF-' . $count;
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    // Helper scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

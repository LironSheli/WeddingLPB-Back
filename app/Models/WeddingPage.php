<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WeddingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'content_json',
        'design_settings',
        'ai_settings',
        'status',
        'price',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'content_json' => 'array',
        'design_settings' => 'array',
        'ai_settings' => 'array',
        'available_from' => 'date',
        'available_until' => 'date',
        'price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug(Str::random(12));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revisionRequests()
    {
        return $this->hasMany(RevisionRequest::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isLive()
    {
        if ($this->status !== 'live') {
            return false;
        }

        $now = now();
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        if ($this->available_until && $now->gt($this->available_until)) {
            $this->update(['status' => 'expired']);
            return false;
        }

        return true;
    }
}


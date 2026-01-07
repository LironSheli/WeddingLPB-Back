<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_page_id',
        'type',
        'amount',
        'status',
        'provider_reference',
        'provider_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_data' => 'array',
    ];

    public function weddingPage()
    {
        return $this->belongsTo(WeddingPage::class);
    }
}


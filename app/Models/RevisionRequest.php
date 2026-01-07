<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_page_id',
        'message',
        'admin_response',
        'quoted_price',
        'deposit_paid',
        'final_paid',
    ];

    protected $casts = [
        'quoted_price' => 'decimal:2',
        'deposit_paid' => 'boolean',
        'final_paid' => 'boolean',
    ];

    public function weddingPage()
    {
        return $this->belongsTo(WeddingPage::class);
    }
}


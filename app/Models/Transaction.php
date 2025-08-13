<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'related_id',
        'related_type',
        'description',
    ];

    // Relacionamento com Wallet
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}

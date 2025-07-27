<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'amount', 'fee', 'status'];

    public function proposal() {
        return $this->belongsTo(Proposal::class);
    }
}

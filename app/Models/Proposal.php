<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'freelancer_id', 'amount', 'duration', 'message', 'links', 'status'];

    protected $casts = ['links' => 'array'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function freelancer() {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }
}

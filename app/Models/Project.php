<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'title', 'description', 'budget', 'deadline', 'status'];

     public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function client() {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function proposals() {
        return $this->hasMany(Proposal::class);
    }
}

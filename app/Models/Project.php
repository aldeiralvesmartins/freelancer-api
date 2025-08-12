<?php

namespace App\Models;

use App\Services\CustomIdService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    public $incrementing = false; // porque o id é string

    protected $keyType = 'string';
    protected $fillable = ['id', 'client_id', 'title', 'description', 'budget', 'deadline', 'status'];
    public static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = CustomIdService::generateCustomId(get_class($model)));
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_project', 'project_id', 'category_id');
    }

    public function client() {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function proposals() {
        return $this->hasMany(Proposal::class);
    }
}

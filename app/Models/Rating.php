<?php

namespace App\Models;

use App\Services\CustomIdService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    public $incrementing = false; // porque o id é string

    protected $keyType = 'string';
    protected $fillable = ['from_user_id', 'to_user_id', 'project_id', 'rating', 'comment'];
    public static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = CustomIdService::generateCustomId(get_class($model)));
    }
    public function fromUser() {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser() {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}

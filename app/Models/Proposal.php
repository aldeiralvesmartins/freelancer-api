<?php

namespace App\Models;

use App\Services\CustomIdService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    public $incrementing = false; // porque o id é string

    protected $keyType = 'string';
    protected $fillable = ['project_id', 'freelancer_id', 'amount', 'duration', 'message', 'links', 'status'];

    protected $casts = ['links' => 'array'];
    public static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->id = CustomIdService::generateCustomId(get_class($model)));
    }
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

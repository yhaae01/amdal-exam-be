<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamBatch extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'exam_id',
        'name',
        'start_time',
        'end_time',
        'max_participants'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'exam_batch_user');
    }

    public function examBatchUsers()
    {
        return $this->hasMany(ExamBatchUser::class);
    }
}

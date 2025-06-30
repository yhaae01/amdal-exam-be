<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExamSubmission extends Model
{
    use HasUuids;
    protected $fillable = [
        'exam_id',
        'user_id',
        'started_at',
        'submitted_at',
        'score',
        'exam_batch_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function activityUsers()
    {
        return $this->hasMany(ActivityUser::class);
    }
}

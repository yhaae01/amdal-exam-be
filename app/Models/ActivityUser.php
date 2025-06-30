<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityUser extends Model
{
    protected $table = 'activity_users';

    protected $fillable = [
        'exam_submission_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function examSubmission()
    {
        return $this->belongsTo(ExamSubmission::class);
    }
}

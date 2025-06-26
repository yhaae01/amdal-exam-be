<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'exam_submission_id',
        'question_id',
        'selected_option_id',
        'answer_text'
    ];
    
    public function exam_submission()
    {
        return $this->belongsTo(ExamSubmission::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Question extends Model
{
    use SoftDeletes, HasUuids;
    
    protected $fillable = [
        'exam_id',
        'question_text',
        'image',
        'order',
        'question_type',
        'weight'
    ];

    public function exam()
    {   
        return $this->belongsTo(Exam::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

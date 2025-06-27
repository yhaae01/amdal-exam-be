<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Exam extends Model
{
    use HasUuids;
    protected $fillable = [
        'title',
        'description',
        'image',
        'question_type',
        'duration'
    ];
    
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function submissions()
    {
        return $this->hasMany(ExamSubmission::class);
    }
    
    public function batches()
    {
        return $this->hasMany(ExamBatch::class);
    }
}

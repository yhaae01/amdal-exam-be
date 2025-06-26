<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct'
    ];
    protected $hidden = ['is_correct'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

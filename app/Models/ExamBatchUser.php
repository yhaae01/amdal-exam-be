<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamBatchUser extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function examBatch()
    {
        return $this->belongsTo(ExamBatch::class, 'exam_batch_id');
    }
    
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}

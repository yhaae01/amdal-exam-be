<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function submissions()
    {
        return $this->hasMany(ExamSubmission::class, 'user_id', 'id');
    }

    public function examBatches()
    {
        return $this->belongsToMany(ExamBatch::class, 'exam_batch_user');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function examBatchUsers()
    {
        return $this->hasOne(ExamBatchUser::class);
    }
    
    public function exams()
    {
        return $this->hasOne(Exam::class);
    }

    public function examBatchUser()
    {
        return $this->hasOne(ExamBatchUser::class, 'user_id', 'id');
    }

    public function exam()
    {
        return $this->hasOneThrough(Exam::class, ExamBatchUser::class, 'user_id', 'id', 'id', 'exam_id');
    }

    public function activityUsers()
    {
        return $this->hasMany(ActivityUser::class);
    }
}

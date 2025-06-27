<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\User;
use App\Models\ExamBatch;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExamBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exam = Exam::first();

        $batch1 = ExamBatch::create([
            'id'               => Str::uuid(),
            'exam_id'          => $exam->id,
            'name'             => 'Sesi 1',
            'start_time'       => Carbon::now()->addMinutes(-10),
            'end_time'         => Carbon::now()->addMinutes(50),
            'max_participants' => 50
        ]);

        $batch2 = ExamBatch::create([
            'id'               => Str::uuid(),
            'exam_id'          => $exam->id,
            'name'             => 'Sesi 2',
            'start_time'       => Carbon::now()->addHours(1),
            'end_time'         => Carbon::now()->addHours(2),
            'max_participants' => 50
        ]);

        $batch3 = ExamBatch::create([
            'id'               => Str::uuid(),
            'exam_id'          => $exam->id,
            'name'             => 'Sesi 3',
            'start_time'       => Carbon::now()->addHours(3),
            'end_time'         => Carbon::now()->addHours(4),
            'max_participants' => 50
        ]);

        // Ambil user berdasarkan email
        $user1 = User::where('email', 'user1@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();
        $user3 = User::where('email', 'user3@example.com')->first();

        $batch1->users()->attach($user1->id);
        $batch2->users()->attach($user2->id);
        $batch3->users()->attach($user3->id);
    }
}

<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Answer;
use App\Models\Question;
use App\Models\ExamBatch;
use Illuminate\Support\Str;
use App\Models\ExamSubmission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExamAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emails = ['user1@example.com', 'user2@example.com', 'user3@example.com'];
        $exam = Exam::first();
        $batch = ExamBatch::where('name', 'Sesi 1')->first();

        if (!$exam || !$batch) {
            dump('Data tidak lengkap: exam atau batch tidak ditemukan.');
            return;
        }

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                dump("User dengan email $email tidak ditemukan.");
                continue;
            }

            $submission = ExamSubmission::create([
                'id'            => Str::uuid(),
                'user_id'       => $user->id,
                'exam_id'       => $exam->id,
                'exam_batch_id' => $batch->id,
                'started_at'    => now()->subMinutes(5),
                'submitted_at'  => null,
            ]);

            $questions = Question::where('exam_id', $exam->id)->get();

            foreach ($questions as $question) {
                $firstOption = $question->options()->first();

                if (!$firstOption) {
                    dump("Soal {$question->id} tidak punya opsi — jawaban dilewati.");
                    continue;
                }

                Answer::create([
                    'id'                 => Str::uuid(),
                    'exam_submission_id' => $submission->id,
                    'question_id'        => $question->id,
                    'selected_option_id' => $firstOption->id,
                    'answer_text'        => null
                ]);
            }
        }
    }

}

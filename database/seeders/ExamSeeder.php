<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exam = Exam::create([
            'title'       => 'Simulasi Ujian Amdal',
            'description' => 'Ujian simulasi untuk calon tenaga teknis operasional Amdalnet.',
            'duration'    => 60
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $question = Question::create([
                'exam_id'       => $exam->id,
                'question_text' => "Pertanyaan ke-$i",
                'question_type' => 'multiple_choice',
                'order'         => $i,
                'weight'        => 1
            ]);

            foreach (['A', 'B', 'C', 'D'] as $label) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => "Jawaban $label",
                    'is_correct'  => $label === 'A'
                ]);
            }
        }
    }
}

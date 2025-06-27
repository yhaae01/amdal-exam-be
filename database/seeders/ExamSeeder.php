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
        Exam::create([
            'title'       => 'Ujian Tenaga Teknis Uji Administrasi',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Uji Administrasi.',
            'duration'    => 60
        ]);
        
        Exam::create([
            'title'       => 'Ujian Tenaga Teknis Helpdesk Amdalnet',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Helpdesk Amdalnet.',
            'duration'    => 60
        ]);

        Exam::create([
            'title'       => 'Ujian Tenaga Teknis Pendukung Persetujuan',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Pendukung Persetujuan.',
            'duration'    => 60
        ]);
        
        Exam::create([
            'title'       => 'Ujian Tenaga Teknis Database Persetujuan Lingkungan',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Database Persetujuan Lingkungan.',
            'duration'    => 60
        ]);

        Exam::create([
            'title'       => 'Ujian Tenaga Teknis IT Hardware/Networking',
            'description' => 'Ujian Online untuk calon Tenaga Teknis IT Hardware/Networking.',
            'duration'    => 60
        ]);

        Exam::create([
            'title'       => 'Ujian Tenaga Teknis Programer Amdalnet',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Programer Amdalnet.',
            'duration'    => 60
        ]);

        // $examTitles = [
        //     'Ujian Tenaga Teknis Uji Administrasi' => 'Ujian Online untuk calon Tenaga Teknis Uji Administrasi.',
        //     'Ujian Tenaga Teknis Helpdesk Amdalnet' => 'Ujian Online untuk calon Tenaga Teknis Helpdesk Amdalnet.',
        //     'Ujian Tenaga Teknis Pendukung Persetujuan' => 'Ujian Online untuk calon Tenaga Teknis Pendukung Persetujuan.',
        //     'Ujian Tenaga Teknis Database Persetujuan Lingkungan' => 'Ujian Online untuk calon Tenaga Teknis Database Persetujuan Lingkungan.',
        //     'Ujian Tenaga Teknis IT Hardware/Networking' => 'Ujian Online untuk calon Tenaga Teknis IT Hardware/Networking.',
        //     'Ujian Tenaga Teknis Programer Amdalnet' => 'Ujian Online untuk calon Tenaga Teknis Programer Amdalnet.',
        // ];

        // foreach ($examTitles as $title => $description) {
        //     $exam = Exam::create([
        //         'title'       => $title,
        //         'description' => $description,
        //         'duration'    => 60 // dalam menit
        //     ]);

        //     // Tambahkan 5 soal ke setiap exam
        //     for ($i = 1; $i <= 5; $i++) {
        //         $question = Question::create([
        //             'exam_id'       => $exam->id,
        //             'question_text' => "Pertanyaan ke-$i",
        //             'question_type' => 'multiple_choice',
        //             'order'         => $i,
        //             'weight'        => 1
        //         ]);

        //         // Tambahkan 4 opsi ke setiap soal
        //         foreach (['A', 'B', 'C', 'D'] as $index => $label) {
        //             Option::create([
        //                 'question_id' => $question->id,
        //                 'option_text' => "Jawaban $label",
        //                 'is_correct'  => $label === 'A'
        //             ]);
        //         }
        //     }
        // }
    }
}

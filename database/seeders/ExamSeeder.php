<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamBatch;
use App\Models\ExamBatchUser;
use App\Models\ExamSubmission;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exam1 = Exam::create([
            'title'       => 'Ujian Tenaga Teknis Uji Administrasi',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Uji Administrasi.',
            'duration'    => 60
        ]);
        
        $exam2 = Exam::create([
            'title'       => 'Ujian Tenaga Teknis Helpdesk Amdalnet',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Helpdesk Amdalnet.',
            'duration'    => 60
        ]);

        $exam3 = Exam::create([
            'title'       => 'Ujian Tenaga Teknis Pendukung Persetujuan',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Pendukung Persetujuan.',
            'duration'    => 60
        ]);
        
        $exam4 = Exam::create([
            'title'       => 'Ujian Tenaga Teknis Database Persetujuan Lingkungan',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Database Persetujuan Lingkungan.',
            'duration'    => 60
        ]);

        $exam5 = Exam::create([
            'title'       => 'Ujian Tenaga Teknis IT Hardware/Networking',
            'description' => 'Ujian Online untuk calon Tenaga Teknis IT Hardware/Networking.',
            'duration'    => 60
        ]);

        $exam6 = Exam::create([
            'title'       => 'Ujian Tenaga Teknis Programmer Amdalnet',
            'description' => 'Ujian Online untuk calon Tenaga Teknis Programmer Amdalnet.',
            'duration'    => 60
        ]);

        // Create Question and Option
        $question1 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Apa itu Ujian Tenaga Teknis Uji Administrasi?',
            'question_type' => 'multiple_choice',
            'order'         => 1,
            'weight'        => 20
        ]);

        $question2 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Apa itu Tenaga Teknis Uji Administrasi?',
            'question_type' => 'multiple_choice',
            'order'         => 5,
            'weight'        => 20
        ]);

        $question3 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Siapa itu Dandun?',
            'question_type' => 'multiple_choice',
            'order'         => 6,
            'weight'        => 20
        ]);

        $question4 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Apakah dandun baik ?',
            'question_type' => 'multiple_choice',
            'order'         => 4,
            'weight'        => 20
        ]);

        $question5 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Dandun Ganteng ga ?',
            'question_type' => 'multiple_choice',
            'order'         => 2,
            'weight'        => 20
        ]);

        $question6 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Deskripsikan tentang dandun ?',
            'question_type' => 'essay',
            'order'         => 7,
            'weight'        => 40
        ]);

        $question7 = Question::create([
            'exam_id'       => $exam6->id,
            'question_text' => 'Apakah dandun cocok dengan mie ayam ?',
            'question_type' => 'essay',
            'order'         => 3,
            'weight'        => 40
        ]);

        $option1 = Option::create([
            'question_id' => $question1->id,
            'option_text' => 'Ujian Tenaga Teknis Uji Administrasi',
            'is_correct'  => true
        ]);

        $option2 = Option::create([
            'question_id' => $question1->id,
            'option_text' => 'Ujian Online untuk calon Tenaga Teknis Uji Administrasi.',
            'is_correct'  => false
        ]);

        $option3 = Option::create([
            'question_id' => $question1->id,
            'option_text' => 'Tenaga Teknis Uji Administrasi',
            'is_correct'  => false
        ]);

        $option4 = Option::create([
            'question_id' => $question1->id,
            'option_text' => 'Tenaga Teknis Uji Administrasi uhuyyyyy.',
            'is_correct'  => false
        ]);

        $option5 = Option::create([
            'question_id' => $question2->id,
            'option_text' => 'Tenaga Teknis Uji Administrasi.',
            'is_correct'  => true
        ]);

        $option6 = Option::create([
            'question_id' => $question2->id,
            'option_text' => 'Ujian Tenaga Teknis Uji Administrasi.',
            'is_correct'  => false
        ]);

        $option7 = Option::create([
            'question_id' => $question2->id,
            'option_text' => 'Ujian Online untuk calon Tenaga Teknis Uji Administrasi.',
            'is_correct'  => false
        ]);

        $option8 = Option::create([
            'question_id' => $question2->id,
            'option_text' => 'Ujian Online untuk calon Tenaga Teknis Uji Administrasi Prikitiw',
            'is_correct'  => false
        ]);

        $option9 = Option::create([
            'question_id' => $question3->id,
            'option_text' => 'Dandun',
            'is_correct'  => true
        ]);

        $option10 = Option::create([
            'question_id' => $question3->id,
            'option_text' => 'Dandun Prikitiw',
            'is_correct'  => false
        ]);

        $option11 = Option::create([
            'question_id' => $question3->id,
            'option_text' => 'Dandun Ganteng',
            'is_correct'  => false
        ]);

        $option12 = Option::create([
            'question_id' => $question3->id,
            'option_text' => 'Dandun Baik',
            'is_correct'  => false
        ]);

        $option13 = Option::create([
            'question_id' => $question4->id,
            'option_text' => 'Ya',
            'is_correct'  => true
        ]);

        $option14 = Option::create([
            'question_id' => $question4->id,
            'option_text' => 'Tidak',
            'is_correct'  => false
        ]);

        $option15 = Option::create([
            'question_id' => $question5->id,
            'option_text' => 'Ya',
            'is_correct'  => true
        ]);

        $option16 = Option::create([
            'question_id' => $question5->id,
            'option_text' => 'Tidak',
            'is_correct'  => false
        ]);

        // Add user in batch
        $exam_batch1 = ExamBatch::create([
            'name'    => 'Batch 1',
            'start_time' => Carbon::now('Asia/Jakarta'),
            'end_time' => Carbon::now('Asia/Jakarta')->addMinutes(60),
            'max_participants' => 50
        ]);

        $exam_batch2 = ExamBatch::create([
            'name'    => 'Batch 2',
            'start_time' => Carbon::now('Asia/Jakarta')->addMinutes(60),
            'end_time' => Carbon::now('Asia/Jakarta')->addMinutes(120),
            'max_participants' => 50
        ]);
        
        $user1 = User::create([
            'name'      => 'User 1',
            'email'     => 'user1@example.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'name'      => 'User 2',
            'email'     => 'user2@example.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'is_active' => true,
        ]);

        $user_exam1 = ExamBatchUser::create([
            'exam_id' => null,
            'exam_batch_id' => $exam_batch2->id,
            'user_id' => $user1->id
        ]);

        $user_exam2 = ExamBatchUser::create([
            'exam_id' => null,
            'exam_batch_id' => $exam_batch2->id,
            'user_id' => $user2->id
        ]);

        // $submission = ExamSubmission::create([
        //     'user_id'       => $user2->id,
        //     'exam_id'       => $exam1->id,
        //     'exam_batch_id' => $exam_batch2->id,
        //     // 'started_at'    => Carbon::now('Asia/Jakarta')->addMinutes(70), 
        // ]);

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

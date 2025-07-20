<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamResultExport implements FromCollection, WithHeadings
{
    protected $keyword;
    protected $year;
    protected $title;

    public function __construct($keyword, $year = 2025, $title = null)
    {
        $this->keyword = $keyword;
        $this->year = $year;
        $this->title = $title;
    }

    public function collection()
    {
        $query = "
            SELECT
                RANK() OVER (
                    PARTITION BY sub.title
                    ORDER BY sub.total_score_fix DESC, sub.duration_seconds ASC
                ) AS ranking,
                sub.name,
                sub.email,
                sub.title,
                sub.total_score_fix,
                sub.submitted_at,
                CONCAT(ROUND(sub.duration_seconds / 60.0), ' Menit') AS duration
            FROM (
                SELECT
                    users.name,
                    users.email,
                    exams.title,
                    exam_submissions.submitted_at,
                    EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) AS duration_seconds,
                    COALESCE(
                        exam_submissions.score,
                        SUM(
                            CASE
                                WHEN selected_option.is_correct = true THEN questions.weight
                                ELSE 0
                            END
                        ) * 5
                    ) AS total_score_fix
                FROM users
                JOIN exam_submissions ON users.id = exam_submissions.user_id
                JOIN exams ON exams.id = exam_submissions.exam_id
                JOIN answers ON exam_submissions.id = answers.exam_submission_id
                JOIN questions ON answers.question_id = questions.id
                LEFT JOIN options AS selected_option ON answers.selected_option_id = selected_option.id
                WHERE users.is_qualified = true
                  AND users.email ILIKE ?
                  AND EXTRACT(YEAR FROM exam_submissions.started_at) = ?
        ";

        $params = ["%{$this->keyword}%", $this->year];

        if (!empty($this->title)) {
            $query .= " AND exams.title ILIKE ? ";
            $params[] = "%{$this->title}%";
        }

        $query .= "
                GROUP BY
                    users.id,
                    exams.title,
                    users.email,
                    users.name,
                    exam_submissions.id,
                    exam_submissions.submitted_at,
                    exam_submissions.started_at,
                    users.is_qualified,
                    exam_submissions.score
            ) AS sub
            ORDER BY sub.total_score_fix DESC, sub.duration_seconds ASC
        ";

        return collect(DB::select($query, $params));
    }

    public function headings(): array
    {
        return [
            'Ranking',
            'Nama',
            'Email',
            'Formasi',
            'Total Nilai',
            'Submit Pada',
            'Waktu Pengerjaan',
        ];
    }
}

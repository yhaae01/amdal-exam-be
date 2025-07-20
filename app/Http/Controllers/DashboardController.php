<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Exam;
use App\Models\ExamBatch;
use Illuminate\Http\Request;
use App\Models\ExamBatchUser;
use App\Models\ExamSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // 2025
            $baseQuery = ExamSubmission::query()
                ->distinct('user_id')
                ->whereYear('started_at', 2025)
                ->whereHas('answers')
                ->whereNotNull('started_at')
                ->with('answer.selectedOption');
    
            $totalUsers = User::where('role', '!=', 'admin')->count();
            $total_exams_2025 = Exam::whereYear('created_at', 2025)->count();
            $totalExamParticipants2025 = $baseQuery->count('user_id');
            $grouping_exam_count = DB::table(DB::raw("({$baseQuery->toSql()}) as es"))
                ->mergeBindings($baseQuery->getQuery())
                ->join('exams as e', 'es.exam_id', '=', 'e.id')
                ->select(
                    'e.title as label',
                    DB::raw('COUNT(DISTINCT es.user_id) as count')
                )
                ->groupBy('es.exam_id', 'e.title')
                ->get();
    
    
            $totalScores = DB::table('exam_submissions')
                ->whereYear('started_at', 2025)
                ->selectRaw("
                    SUM(
                        COALESCE(
                            score,
                            (
                                SELECT SUM(
                                    CASE
                                        WHEN options.is_correct = true THEN questions.weight
                                        ELSE 0
                                    END
                                ) * 5
                                FROM answers
                                JOIN questions ON answers.question_id = questions.id
                                LEFT JOIN options ON answers.selected_option_id = options.id
                                WHERE answers.exam_submission_id = exam_submissions.id
                            )
                        )
                    ) AS total_score
                ")->first()->total_score ?? 0;
    
    
            return apiResponse([
                    'highlight' => [
                        'total_users' => $totalUsers,
                        'total_exam_2025' => $total_exams_2025,
                        'total_exam_participants_2025' => $totalExamParticipants2025,
                        'grouping_exam_count_2025' => $grouping_exam_count,
                    ],
                ], 'berhasil mengambil data', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage());
            return apiResponse(null, $e->getMessage(), false, 500);
        }
    }

    public function top_score_exam(Request $request) {
        try {
            // 2025
            $year = $request->query('year');
            $title = $request->query('title');

            $query = "
                SELECT
                    users.name,
                    users.email,
                    exams.title,
                    exam_submissions.started_at,
                    users.is_qualified,
                    exam_submissions.submitted_at,
                    SUM(
                        CASE
                            WHEN selected_option.is_correct = true THEN questions.weight
                            ELSE 0
                        END
                    ) AS exam_submission_score,
                    COALESCE(
                        exam_submissions.score,
                        SUM(
                            CASE
                                WHEN selected_option.is_correct = true THEN questions.weight
                                ELSE 0
                            END
                        ) * 5
                    ) AS total_score_fix,
                    CASE
                        WHEN exam_submissions.submitted_at IS NULL THEN '15 Menit'
                        ELSE CONCAT(
                            ROUND(EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) / 60.0),
                            ' Menit'
                        )
                    END AS duration,
                    RANK() OVER (
                        PARTITION BY exams.title
                        ORDER BY
                            COALESCE(
                                exam_submissions.score,
                                SUM(
                                    CASE
                                        WHEN selected_option.is_correct = true THEN questions.weight
                                        ELSE 0
                                    END
                                ) * 5
                            ) DESC,
                            EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) ASC
                    ) AS ranking
                FROM users
                JOIN exam_submissions ON users.id = exam_submissions.user_id
                JOIN exams ON exams.id = exam_submissions.exam_id
                JOIN answers ON exam_submissions.id = answers.exam_submission_id
                JOIN questions ON answers.question_id = questions.id
                LEFT JOIN options AS selected_option ON answers.selected_option_id = selected_option.id
                WHERE EXTRACT(YEAR FROM exam_submissions.started_at) = ?
            ";

            $params = [$year];

            if (!empty($title)) {
                $query .= " AND exams.title ILIKE ? ";
                $params[] = "%$title%";
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
                ORDER BY
                    total_score_fix DESC,
                    EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) ASC
                    LIMIT 10
            ";

            $results = DB::select($query, $params);

            return apiResponse($results, 'Top User get successfully', true, 200);

        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage());
            return apiResponse(null, $e->getMessage(), false, 500);
        }
    }

    public function current_batch_list() {
        try {
            $now = Carbon::now();
            $startOf2025 = Carbon::create(2025, 1, 1)->startOfDay();
            $endOf2025 = Carbon::create(2025, 12, 31)->endOfDay();

            // 1. Sesi Sedang Berlangsung
            $sedangBerlangsung = ExamBatch::whereBetween('start_time', [$startOf2025, $endOf2025])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

            // 2. Sesi Akan Dimulai
            $akanDimulai = ExamBatch::whereBetween('start_time', [$startOf2025, $endOf2025])
            ->where('start_time', '>', $now)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

            // 3. Sesi Sudah Selesai
            $sudahSelesai = ExamBatch::whereBetween('start_time', [$startOf2025, $endOf2025])
            ->where('end_time', '<', $now)
            ->orderByDesc('end_time')
            ->limit(5)
            ->get();

            $batch = $sedangBerlangsung->map(fn ($b) => [
                'type' => 'Ongoing',
                'slug' => 'ongoing',
                'data' => $b,
            ])
            ->concat($akanDimulai->map(fn ($b) => [
                'type' => 'Upcoming',
                'slug' => 'upcoming',
                'data' => $b,
            ]))
            ->concat($sudahSelesai->map(fn ($b) => [
                'type' => 'Finish',
                'slug' => 'finish',
                'data' => $b,
            ])); 

            return apiResponse($batch, 'List Batch get successfully', true, 200);

        } catch (\Exception $e) {
            Log::error('Failed to get current batch list: ' . $e->getMessage());
            return apiResponse(null, $e->getMessage(), false, 500);
        }
    }
}

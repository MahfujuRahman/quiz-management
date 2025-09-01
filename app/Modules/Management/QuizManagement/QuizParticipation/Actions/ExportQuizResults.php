<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Actions;

use Maatwebsite\Excel\Facades\Excel;

class ExportQuizResults
{
    static $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request, $quizId)
    {
        try {
            // Validate quiz exists
            $quiz = self::$quizModel::find($quizId);
            if (!$quiz) {
                return messageResponse('Quiz not found', [], 404, 'not_found');
            }

            // Get all completed participations
            $participations = self::$participationModel::where('quiz_id', $quizId)
                ->where('is_completed', true)
                ->orderBy('percentage', 'desc')
                ->get();

            if ($participations->isEmpty()) {
                return messageResponse('No completed submissions found', [], 404, 'no_data');
            }

            $excelData = new QuizResultsExport($participations, $quiz);
            $fileName = 'quiz_results_' . $quiz->title . '_' . date('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download($excelData, $fileName);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}

class QuizResultsExport implements \Maatwebsite\Excel\Concerns\FromCollection, 
                                  \Maatwebsite\Excel\Concerns\WithHeadings,
                                  \Maatwebsite\Excel\Concerns\WithStyles,
                                  \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    protected $participations;
    protected $quiz;

    public function __construct($participations, $quiz)
    {
        $this->participations = $participations;
        $this->quiz = $quiz;
    }

    public function collection()
    {
        return $this->participations->map(function ($participation, $index) {
            return [
                'sl' => $index + 1,
                'name' => $participation->name,
                'email' => $participation->email,
                'phone' => $participation->phone,
                'organization' => $participation->organization,
                'obtained_marks' => $participation->obtained_marks,
                'total_marks' => $this->quiz->total_mark,
                'percentage' => round($participation->percentage, 2) . '%',
                'status' => $participation->is_passed ? 'PASSED' : 'FAILED',
                'duration' => $this->formatDuration($participation->duration),
                'submit_reason' => $participation->submit_reason,
                'submitted_at' => $participation->submitted_at ? $participation->submitted_at->format('Y-m-d H:i:s') : 'N/A'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SL',
            'Name',
            'Email',
            'Phone',
            'Organization',
            'Obtained Marks',
            'Total Marks',
            'Percentage',
            'Status',
            'Duration',
            'Submit Reason',
            'Submitted At'
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatDuration($duration)
    {
        if (!$duration) return 'N/A';
        
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}

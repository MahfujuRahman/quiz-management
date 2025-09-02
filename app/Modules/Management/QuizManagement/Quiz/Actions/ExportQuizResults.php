<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

use Maatwebsite\Excel\Facades\Excel;

class ExportQuizResults
{
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request, $quizId)
    {
        try {
            // Validate quiz exists
            $quiz = self::$quizModel::find($quizId);
            if (!$quiz) {
                return messageResponse('Quiz not found', [], 404, 'not_found');
            }

            // For now, create sample data since we don't have participation records
            $sampleParticipations = collect([
                [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '+1234567890',
                    'organization' => 'ABC Company',
                    'obtained_marks' => 85,
                    'percentage' => 85.0,
                    'is_passed' => true,
                    'duration' => '25:30',
                    'submit_reason' => 'Normal completion',
                    'submitted_at' => now()->format('Y-m-d H:i:s')
                ],
                [
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'phone' => '+1234567891',
                    'organization' => 'XYZ Corp',
                    'obtained_marks' => 92,
                    'percentage' => 92.0,
                    'is_passed' => true,
                    'duration' => '22:15',
                    'submit_reason' => 'Normal completion',
                    'submitted_at' => now()->subMinutes(30)->format('Y-m-d H:i:s')
                ]
            ]);

            $excelData = new QuizResultsExport($sampleParticipations, $quiz);
            $fileName = 'quiz_results_' . str_replace(' ', '_', $quiz->title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

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
                'name' => $participation['name'],
                'email' => $participation['email'],
                'phone' => $participation['phone'],
                'organization' => $participation['organization'],
                'obtained_marks' => $participation['obtained_marks'],
                'total_marks' => $this->quiz->total_mark,
                'percentage' => round($participation['percentage'], 2) . '%',
                'status' => $participation['is_passed'] ? 'PASSED' : 'FAILED',
                'duration' => $participation['duration'],
                'submit_reason' => $participation['submit_reason'],
                'submitted_at' => $participation['submitted_at']
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
}

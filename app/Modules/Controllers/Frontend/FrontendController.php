<?php

namespace App\Modules\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontendController extends Controller
{

    public function HomePage()
    {
        return Inertia::render('HomePage/Index', [
            'event' => [
                'title' => 'Home Page',
            ]
        ]);
    }
    public function QuizList()
    {
        return Inertia::render('HomePage/QuizList', [
            'event' => [
                'title' => 'Quiz List',
            ]
        ]);
    }

    public function QuizPage()
    {
        return Inertia::render('Quiz/Exam', [
            'event' => [
                'title' => 'Quiz Page',
            ]
        ]);
    }
    public function Register()
    {
        return Inertia::render('Quiz/Registration', [
            'event' => [
                'title' => 'Quiz Registration',
            ]
        ]);
    }
    public function Result()
    {
        return Inertia::render('Admin/ResultsExport', [
            'event' => [
                'title' => 'Quiz Result',
            ]
        ]);
    }
}

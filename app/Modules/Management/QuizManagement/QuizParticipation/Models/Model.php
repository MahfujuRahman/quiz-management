<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Model extends EloquentModel
{
    use SoftDeletes;
    
    protected $table = "quiz_participations";
    protected $guarded = [];
    
    protected $casts = [
        'student_info' => 'array',
        'answers' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($data) {
            $random_no = random_int(100, 999) . $data->id . random_int(100, 999);
            $slug = "participation_" . $data->quiz_id . "_" . $random_no;
            $data->slug = Str::slug($slug);
            if (strlen($data->slug) > 50) {
                $data->slug = substr($data->slug, strlen($data->slug) - 50, strlen($data->slug));
            }
            $data->save();
        });
    }

    public function quiz()
    {
        return $this->belongsTo("App\Modules\Management\QuizManagement\Quiz\Models\Model", 'quiz_id', 'id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeCompleted($q)
    {
        return $q->where('is_completed', true);
    }

    public function scopeOngoing($q)
    {
        return $q->where('is_completed', false);
    }
}

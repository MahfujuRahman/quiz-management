<?php
namespace App\Modules\Management\QuizManagement\QuizSubmissionResult\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="App\Modules\Management\QuizManagement\QuizSubmissionResult\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\QuizManagement\QuizSubmissionResult\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        for ($i = 1; $i <= 100; $i++) {
            self::$model::create([                'user_id' => null,
                'quiz_id' => null,
                'submission_no' => $faker->boolean,
                'course_module_class_id' => null,
                'quiz_mark' => $faker->randomFloat(2, 0, 1000),
                'obtain_mark' => $faker->randomFloat(2, 0, 1000),
                'submission_datetime' => $faker->dateTime,
            ]);
        }
    }
}
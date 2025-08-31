<?php
namespace App\Modules\Management\QuizManagement\QuizQuestion\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="App\Modules\Management\QuizManagement\QuizQuestion\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\QuizManagement\QuizQuestion\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        for ($i = 1; $i <= 100; $i++) {
            self::$model::create([                'quiz_question_topic_id' => null,
                'title' => $faker->text(255),
                'question_level' => $faker->randomElement(array (
  0 => 'easy',
  1 => 'medium',
  2 => 'hard',
)),
                'mark' => $faker->randomFloat(2, 0, 1000),
                'is_multiple' => $faker->boolean,
                'session_year' => $faker->text(20),
            ]);
        }
    }
}
<?php
namespace App\Modules\Management\QuizManagement\Quiz\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="App\Modules\Management\QuizManagement\Quiz\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        for ($i = 1; $i <= 100; $i++) {
            self::$model::create([                'title' => $faker->text(255),
                'description' => $faker->paragraph,
                'total_question' => $faker->randomNumber,
                'exam_start_datetime' => $faker->dateTime,
                'exam_end_datetime' => $faker->dateTime,
                'total_mark' => $faker->randomFloat(2, 0, 1000),
                'pass_mark' => $faker->randomFloat(2, 0, 1000),
                'is_negative_marking' => $faker->boolean,
                'negative_value' => $faker->randomFloat(2, 0, 1000),
            ]);
        }
    }
}
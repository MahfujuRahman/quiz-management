<?php
namespace App\Modules\Management\QuizManagement\QuizParticipation\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="App\Modules\Management\QuizManagement\QuizParticipation\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        for ($i = 1; $i <= 100; $i++) {
            self::$model::create([                'quiz_id' => null,
                'session_token' => $faker->text(255),
                'name' => $faker->sentence,
                'email' => $faker->sentence,
                'phone' => $faker->sentence,
                'organization' => $faker->sentence,
                'answers' => json_encode([$faker->word, $faker->word]),
                'obtained_marks' => $faker->randomFloat(2, 0, 1000),
                'percentage' => $faker->randomFloat(2, 0, 1000),
                'duration' => null,
                'submit_reason' => $faker->sentence,
                'is_completed' => $faker->boolean,
                'is_passed' => $faker->boolean,
                'started_at' => $faker->dateTime,
                'submitted_at' => $faker->dateTime,
            ]);
        }
    }
}
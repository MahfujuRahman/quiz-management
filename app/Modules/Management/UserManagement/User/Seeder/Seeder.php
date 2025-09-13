<?php

namespace App\Modules\Management\UserManagement\User\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="\App\Modules\Management\UserManagement\User\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

          self::$model::create([
            'first_name' => "super",
            'last_name' => "super",
            'email' => "superadmin@gmail.com",
            'password' => Hash::make('@12345678'),
            'image' => 'avatar.png',
            'role_id' => 1,
        ]);
        self::$model::create([
            'first_name' => "admin",
            'last_name' => "admin",
            'email' => "admin@gmail.com",
            'password' => Hash::make('@12345678'),
            'image' => 'avatar.png',
            'role_id' => 2,
        ]);
    }
}

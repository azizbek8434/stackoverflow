<?php

use App\User;
use App\Question;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        factory(User::class, 3)->create()->each(function ($user) {
            $user->questions()
                ->saveMany(
                    factory(Question::class, rand(3, 5))->make()
                );
        });
    }
}

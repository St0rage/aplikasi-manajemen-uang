<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Dani Yudistira Maulana',
            'email' => 'daniyudistira25@gmail.com',
            'password' => Hash::make('dani12345')
        ]);

        \App\Models\Saving::create([
            'user_id' => 1,
            'saving_name' => 'Tabungan Pribadi',
            'saving_total' => 10000000
        ]);
    }
}

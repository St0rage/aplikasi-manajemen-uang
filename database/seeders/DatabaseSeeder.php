<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Balance;
use App\Models\Role;
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
            'password' => Hash::make('dani12345'),
            'is_admin' => 1
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Veronica Christine',
        //     'email' => 'veronica25@gmail.com',
        //     'password' => Hash::make('vero12345')
        // ]);

        Balance::create([
            'user_id' => 1
        ]);

        // Balance::create([
        //     'user_id' => 2
        // ]);



        // \App\Models\PiggyBank::create([
        //     'user_id' => 1,
        //     'saving_name' => 'Tabungan Pribadi',
        //     'saving_total' => 10000000
        // ]);
    }
}

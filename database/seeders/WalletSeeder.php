<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $users = \App\Models\User::all();

    foreach ($users as $user) {
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 1000,
            ]);
        }
    }
    }
}

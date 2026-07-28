<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Santri;
use App\Models\AbsensiSubuh;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin & Operator
        Admin::firstOrCreate(
            ['username' => 'abdullah'],
            [
                'nama' => 'Ust. Abdullah',
                'email' => 'abdullah@pesantren.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        Admin::firstOrCreate(
            ['username' => 'ahmad'],
            [
                'nama' => 'Ust. Ahmad',
                'email' => 'ahmad@pesantren.com',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ]
        );

        // 2. Start from 0: No dummy santris. Database is ready for live sensor testing.
    }
}

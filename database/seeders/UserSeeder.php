<?php

namespace Database\Seeders;

use App\Models\User;
use App\Imports\UsersImport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Administrator',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Admin PDLUK',
            'email'     => 'pbtpdluk@gmail.com',
            'password'  => Hash::make('@mdal123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Admin 2 PDLUK',
            'email'     => 'amdal@gmail.com',
            'password'  => Hash::make('@mdal123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // import users from Excel file
        // Excel::import(new UsersImport, database_path('seeders/data/template_peserta_ujian.xlsx'));

        // User::create([
        //     'name'      => 'User 3',
        //     'email'     => 'user3@example.com',
        //     'password'  => Hash::make('password'),
        //     'role'      => 'user',
        //     'is_active' => true,
        // ]);
    }
}

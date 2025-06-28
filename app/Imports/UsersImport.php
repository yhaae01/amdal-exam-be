<?php

namespace App\Imports;

use App\Models\User;
use App\Models\ExamBatchUser;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return User::firstOrCreate(
            ['email' => $row['email']],
            [
                'name'      => $row['name'],
                'password'  => Hash::make('Amdal123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );
    }
}

<?php

namespace App\Imports;

use App\Models\User;
use App\Models\ExamBatchUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
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

    public function chunkSize(): int
    {
        return 100; // proses 100 baris per job
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                Log::info('✅ Import users selesai.');
            },
            ImportFailed::class => function (\Throwable $e) {
                Log::error('❌ Import users gagal: ' . $e->getMessage());
            },
        ];
    }
}

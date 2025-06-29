<?php

namespace App\Imports;

use App\Mail\UserNotificationMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UserEmailBlastImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!empty($row['email']) && !empty($row['name']) && !empty($row['formasi'])) {
                try {
                    Mail::to($row['email'])->queue(new UserNotificationMail(
                        $row['name'],
                        $row['formasi']
                    ));
                    Log::info("Email dikirim ke: {$row['email']} ({$row['formasi']})");
                } catch (\Exception $e) {
                    Log::error("Gagal kirim ke {$row['email']}: " . $e->getMessage());
                }
            }
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}

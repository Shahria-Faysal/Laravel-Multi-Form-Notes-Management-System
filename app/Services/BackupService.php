<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BackupService
{
    public static function runBackup(int $interval = 0, bool $is_instant = false)
    {
        try {

            $host = '127.0.0.1';
            $port = '3306';
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $mysqldump = 'D:\\PROGRAMMING\\Databse\\Xampp\\mysql\\bin\\mysqldump.exe';
            $backupDir = storage_path('app\\backups');

            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupPath = $backupDir . '\\' . $database . '_' . now()->format('Y-m-d_H-i-s') . '.sql';

            $command = "\"{$mysqldump}\" -h {$host} -P {$port} -u {$username} {$database} > \"{$backupPath}\" 2>&1";

            shell_exec($command);

            if (file_exists($backupPath) && filesize($backupPath) > 0) {

                DB::table('backup_logs')->insert([
                    'filename' => basename($backupPath),
                    'status' => 'success',
                    'interval' => $interval,
                    'is_instant' => $is_instant,
                    'created_at' => now(),
                ]);

                return true;
            }

            throw new \Exception('Backup file not created');

        } catch (\Exception $e) {

            DB::table('backup_logs')->insert([
                'filename' => 'failed_' . now()->format('Y-m-d_H-i-s'),
                'status' => 'failed',
                'interval' => $interval,
                'is_instant' => $is_instant,
                'created_at' => now(),
            ]);

            return false;
        }
    }


    public static function runUserBackup(int $userId, ?string $label = '', bool $isInstant = false, int $interval = 0)
    {
        try {
            $host = '127.0.0.1';
            $port = '3306';
            // $database = env('DB_DATABASE');
            // $username = env('DB_USERNAME');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $mysqldump = 'D:\\PROGRAMMING\\Databse\\Xampp\\mysql\\bin\\mysqldump.exe';
            $backupDir = storage_path('app\\backups\\users\\' . $userId);

            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filename = 'user_' . $userId . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $backupPath = $backupDir . '\\' . $filename;

            // dumps only the notes table rows belonging to this user
            // try single quotes instead
            $command = "\"{$mysqldump}\" -h {$host} -P {$port} -u {$username} {$database} notes "
                . "--where=\"user_id={$userId}\" > \"{$backupPath}\" 2>&1";

            shell_exec($command);

            if (file_exists($backupPath) && filesize($backupPath) > 0) {
                DB::table('backup_logs')->insert([
                    'filename' => $filename,
                    'status' => 'success',
                    'interval' => $interval,
                    'user_id' => $userId,
                    'label' => $label,
                    'is_instant' => $isInstant,
                    'created_at' => now(),
                ]);

                return true;
            }

            throw new \Exception('User backup file not created');

        } catch (\Exception $e) {
            DB::table('backup_logs')->insert([
                'filename' => 'failed_' . now()->format('Y-m-d_H-i-s'),
                'status' => 'failed',
                'interval' => $interval,
                'user_id' => $userId,
                'label' => $label,
                'is_instant' => $isInstant,
                'created_at' => now(),
            ]);

            return false;
        }
    }
}
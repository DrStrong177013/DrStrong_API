<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class ClearLogIfTooLarge extends Command
{
    // Tên lệnh Artisan bạn sẽ gọi
    protected $signature = 'log:clear-if-too-large';

    // Mô tả lệnh
    protected $description = 'Clear the Laravel log if it exceeds a specified size limit';

    // Giới hạn dung lượng file log (tính bằng bytes). Ví dụ: 5MB (5 * 1024 * 1024)
    protected $maxLogSize = 100 * 1024 * 1024; // 5MB

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Đường dẫn đến file log
        $logPath = storage_path('logs/laravel.log');

        // Kiểm tra nếu file log tồn tại
        if (File::exists($logPath)) {
            // Lấy kích thước file log
            $logSize = File::size($logPath);

            // Kiểm tra nếu file log vượt quá giới hạn dung lượng
            if ($logSize > $this->maxLogSize) {
                // Làm trống file log
                File::put($logPath, '');

                // Hiển thị thông báo trong console
                $this->info('The log file has been cleared because it exceeded ' . ($this->maxLogSize / (1024 * 1024)) . ' MB.');
            } else {
                // Hiển thị thông báo nếu log chưa vượt quá giới hạn
                $this->info('The log file size is within the limit.');
            }
        } else {
            $this->error('Log file does not exist.');
        }
    }
}
//php artisan log:clear-if-too-large
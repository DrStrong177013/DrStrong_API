<?php

namespace App\Listeners;

use App\Events\TestCaseResultsUpdated;
use Illuminate\Support\Facades\Log;

class TestCaseResultsUpdatedListener
{
    public function handle(TestCaseResultsUpdated $event)
    {
        // Ghi log để kiểm tra listener đã được gọi hay chưa
        Log::info('Handling TestCaseResultsUpdated event', ['results' => $event->results]);

        // Thực hiện logic của bạn ở đây
    }
}


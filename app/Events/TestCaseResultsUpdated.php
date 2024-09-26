<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestCaseResultsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $results;

    public function __construct($results)
    {
        $this->results = $results;
        // Thêm log để theo dõi thông tin khi khởi tạo sự kiện
        Log::info('Initializing TestCaseResultsUpdated event', ['results' => $this->results]);
    }

    public function broadcastOn()
    {
        // Thêm log để ghi lại kênh broadcasting
        Log::info('Broadcasting on channel: test-case-results');
        return new Channel('test-case-results'); // Đặt tên kênh đúng
    }

    public function broadcastAs()
    {
        // Thêm log để ghi lại tên sự kiện
        Log::info('Broadcasting event as: TestCaseResultsUpdated');
        return 'TestCaseResultsUpdated'; // Tên sự kiện
    }
}

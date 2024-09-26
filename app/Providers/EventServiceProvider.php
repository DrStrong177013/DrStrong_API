<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TestCaseResultsUpdated; // Đảm bảo import sự kiện
use App\Listeners\TestCaseResultsUpdatedListener; // Đảm bảo import listener

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TestCaseResultsUpdated::class => [
            TestCaseResultsUpdatedListener::class,
        ],
    ];

    public function boot()
    {
        //
    }
}

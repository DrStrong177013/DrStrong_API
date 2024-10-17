<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TestCaseResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class TestResultsComponent extends Component
{
    public $results;
    protected $listeners = ['jobCompleted' => 'refreshResults'];
    public $jobCompleted;

    public function mount()
    {
        sleep(2.5);
        // Lấy dữ liệu từ cơ sở dữ liệu
        log::info('mount results');
        $this->results = TestCaseResult::all()->toArray(); // Chuyển thành mảng

    }


    public function refreshChart()
    {

        $this->jobId = 'Hello';
        log::info('refresh results');
        $this->results = TestCaseResult::all()->toArray();

        // $jobExists = DB::table('job_statuses')->where('job_id', $this->jobId)->exists();

        // if (!$jobExists) {
        //     sleep(2);
        //     $jobExists = DB::table('job_statuses')->where('job_id', $this->jobId)->exists();
        // }
        // if (!$jobExists) {
        //     dd($jobExists);
        //     log::info('Job does not exist, stopping polling.');
        //     $this->dispatch('refreshChildComponent', $this->results);
        //     $this->dispatch('refreshChart');
        //     return; // Ngừng polling nếu job không tồn tại
        // }


        $this->dispatch('refreshChildComponent', $this->results);
        $this->dispatch('refreshChart');

        try {
            // Kiểm tra trạng thái job từ cơ sở dữ liệu
            $status = DB::table('job_statuses')->where('job_id', $this->jobId)->value('status');

            if ($status === 'completed') {
                // DB::table('job_statuses')->where('job_id', $this->jobId)->delete();

                $this->dispatch('jobCompleted', true);
            }

        } catch (\Exception $e) {
            \Log::error('Error updating chart data: ' . $e->getMessage());
        }

    }


    public function render()
    {
        $results = $this->results;
        log::info('render results');
        return view('livewire.test-results-component');
    }
}

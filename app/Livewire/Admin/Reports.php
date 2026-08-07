<?php

namespace App\Livewire\Admin;

use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Reports extends Component
{
    public function markReviewed(int $reportId): void
    {
        Report::whereKey($reportId)->update(['status' => Report::STATUS_REVIEWED]);
    }

    public function close(int $reportId): void
    {
        Report::whereKey($reportId)->update(['status' => Report::STATUS_CLOSED]);
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'reports' => Report::with(['reporter', 'reportedUser', 'order'])->latest()->get(),
        ]);
    }
}

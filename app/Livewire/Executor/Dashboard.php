<?php

namespace App\Livewire\Executor;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Dashboard extends Component
{
    public function render()
    {
        $userId = Auth::id();

        $activeJobs = Order::whereHas('acceptedOffer', fn ($q) => $q->where('executor_id', $userId))
            ->where('status', Order::STATUS_IN_PROGRESS)
            ->with(['category', 'customer', 'acceptedOffer'])
            ->latest()
            ->get();

        $completedJobsCount = Order::whereHas('acceptedOffer', fn ($q) => $q->where('executor_id', $userId))
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        $totalEarned = Payment::where('type', Payment::TYPE_PAYOUT)
            ->where('status', Payment::STATUS_SUCCESS)
            ->whereHas('order.acceptedOffer', fn ($q) => $q->where('executor_id', $userId))
            ->sum('amount');

        $availableOrders = Order::where('status', Order::STATUS_OPEN)
            ->whereDoesntHave('offers', fn ($q) => $q->where('executor_id', $userId))
            ->with(['category', 'city'])
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.executor.dashboard', [
            'activeJobs' => $activeJobs,
            'completedJobsCount' => $completedJobsCount,
            'totalEarned' => $totalEarned,
            'availableOrders' => $availableOrders,
        ]);
    }
}

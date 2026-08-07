<?php

namespace App\Livewire\Executor;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Dashboard extends Component
{
    public string $period = 'week';

    private function periodStart(): Carbon
    {
        return match ($this->period) {
            'day' => now()->startOfDay(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };
    }

    /** Amount actually kept by the executor for one completed order (payout net, or full price if cash). */
    private function earningsFor(Order $order): float
    {
        $payout = $order->payments->first(fn ($p) => $p->type === Payment::TYPE_PAYOUT && $p->status === Payment::STATUS_SUCCESS);

        if ($payout) {
            return (float) $payout->amount;
        }

        $cash = $order->payments->first(fn ($p) => $p->type === Payment::TYPE_CHARGE && $p->status === Payment::STATUS_SUCCESS && $p->provider === 'cash');

        return $cash ? (float) $order->acceptedOffer->price : 0.0;
    }

    public function render()
    {
        $userId = Auth::id();

        $activeJobs = Order::whereHas('acceptedOffer', fn ($q) => $q->where('executor_id', $userId))
            ->where('status', Order::STATUS_IN_PROGRESS)
            ->with(['category', 'customer', 'acceptedOffer'])
            ->latest()
            ->get();

        $completedAll = Order::whereHas('acceptedOffer', fn ($q) => $q->where('executor_id', $userId))
            ->where('status', Order::STATUS_COMPLETED)
            ->with(['acceptedOffer', 'payments', 'category'])
            ->get();

        $completedInPeriod = $completedAll->filter(
            fn ($order) => $order->completed_at && $order->completed_at->gte($this->periodStart())
        );

        $earnedInPeriod = $completedInPeriod->sum(fn ($order) => $this->earningsFor($order));

        $availableOrders = Order::where('status', Order::STATUS_OPEN)
            ->whereDoesntHave('offers', fn ($q) => $q->where('executor_id', $userId))
            ->with(['category', 'city'])
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.executor.dashboard', [
            'activeJobs' => $activeJobs,
            'completedJobsCount' => $completedInPeriod->count(),
            'completedTotalCount' => $completedAll->count(),
            'totalEarned' => $earnedInPeriod,
            'recentCompleted' => $completedInPeriod->sortByDesc('completed_at')->take(10),
            'availableOrders' => $availableOrders,
        ]);
    }
}

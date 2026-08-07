<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class MyOrders extends Component
{
    public string $tab = 'customer';

    private const ACTIVE_STATUSES = [Order::STATUS_OPEN, Order::STATUS_IN_PROGRESS, Order::STATUS_DISPUTED];

    public function render()
    {
        $customerOrders = Order::where('customer_id', Auth::id())->with('category')->withCount('offers')->latest()->get();

        $executorOrders = Order::whereHas('offers', fn ($q) => $q->where('executor_id', Auth::id()))
            ->with(['category', 'offers' => fn ($q) => $q->where('executor_id', Auth::id())])
            ->latest()
            ->get();

        return view('livewire.orders.my-orders', [
            'customerActive' => $customerOrders->whereIn('status', self::ACTIVE_STATUSES),
            'customerHistory' => $customerOrders->whereNotIn('status', self::ACTIVE_STATUSES),
            'executorActive' => $executorOrders->whereIn('status', self::ACTIVE_STATUSES),
            'executorHistory' => $executorOrders->whereNotIn('status', self::ACTIVE_STATUSES),
        ]);
    }
}

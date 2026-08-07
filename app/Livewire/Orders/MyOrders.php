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

    private const ACTIVE_STATUSES = [
        Order::STATUS_PENDING_PAYMENT, Order::STATUS_OPEN, Order::STATUS_IN_PROGRESS, Order::STATUS_DISPUTED,
    ];

    public function repeatOrder(int $orderId)
    {
        $order = Order::where('customer_id', Auth::id())->findOrFail($orderId);

        $new = Order::create([
            'customer_id' => Auth::id(),
            'category_id' => $order->category_id,
            'city_id' => $order->city_id,
            'title' => $order->title,
            'description' => $order->description,
            'address' => $order->address,
            'budget_min' => $order->budget_min,
            'budget_max' => $order->budget_max,
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        return redirect()->route('platform-fee.checkout', $new);
    }

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

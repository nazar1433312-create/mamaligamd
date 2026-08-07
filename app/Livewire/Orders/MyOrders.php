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

    public function render()
    {
        $asCustomer = Order::where('customer_id', Auth::id())->with('category')->withCount('offers')->latest()->get();

        $asExecutor = Order::whereHas('offers', fn ($q) => $q->where('executor_id', Auth::id()))
            ->with(['category', 'offers' => fn ($q) => $q->where('executor_id', Auth::id())])
            ->latest()
            ->get();

        return view('livewire.orders.my-orders', [
            'asCustomer' => $asCustomer,
            'asExecutor' => $asExecutor,
        ]);
    }
}

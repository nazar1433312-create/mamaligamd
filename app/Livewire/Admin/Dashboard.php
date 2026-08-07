<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'usersCount' => User::count(),
            'ordersCount' => Order::count(),
            'openOrders' => Order::where('status', Order::STATUS_OPEN)->count(),
            'disputedOrders' => Order::where('status', Order::STATUS_DISPUTED)->count(),
            'gmv' => Order::where('status', Order::STATUS_COMPLETED)->sum('budget_max'),
        ]);
    }
}

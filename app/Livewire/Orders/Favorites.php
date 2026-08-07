<?php

namespace App\Livewire\Orders;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Favorites extends Component
{
    public function render()
    {
        $orders = Auth::user()->favoriteOrders()
            ->with(['category', 'city'])
            ->withCount('offers')
            ->orderByDesc('favorites.created_at')
            ->get();

        return view('livewire.orders.favorites', ['orders' => $orders]);
    }
}

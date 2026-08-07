<?php

namespace App\Livewire\Orders;

use App\Models\Favorite;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FavoriteButton extends Component
{
    public Order $order;

    public bool $favorited = false;

    public function mount(Order $order): void
    {
        $this->order = $order;
        $this->favorited = Auth::check()
            && Favorite::where('user_id', Auth::id())->where('order_id', $order->id)->exists();
    }

    public function toggle(): void
    {
        abort_unless(Auth::check(), 403);

        $favorite = Favorite::where('user_id', Auth::id())->where('order_id', $this->order->id)->first();

        if ($favorite) {
            $favorite->delete();
            $this->favorited = false;
        } else {
            Favorite::create(['user_id' => Auth::id(), 'order_id' => $this->order->id]);
            $this->favorited = true;
        }
    }

    public function render()
    {
        return view('livewire.orders.favorite-button');
    }
}

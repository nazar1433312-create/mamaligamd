<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.site')]
class Orders extends Component
{
    use WithPagination;

    public string $status = '';

    public function forceCancel(int $orderId): void
    {
        Order::whereKey($orderId)->update(['status' => Order::STATUS_CANCELLED, 'cancelled_at' => now()]);
    }

    public function markDisputed(int $orderId): void
    {
        Order::whereKey($orderId)->update(['status' => Order::STATUS_DISPUTED]);
    }

    public function resolveDispute(int $orderId, string $resolution): void
    {
        if ($resolution === 'complete') {
            Order::whereKey($orderId)->update(['status' => Order::STATUS_COMPLETED, 'completed_at' => now()]);
        } else {
            Order::whereKey($orderId)->update(['status' => Order::STATUS_CANCELLED, 'cancelled_at' => now()]);
        }
    }

    public function deleteOrder(int $orderId): void
    {
        Order::whereKey($orderId)->delete();
        $this->resetPage();
    }

    public function deleteAll(): void
    {
        Order::query()->delete();
        $this->resetPage();
    }

    public function render()
    {
        $orders = Order::query()
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with(['customer', 'category'])
            ->latest()
            ->paginate(20);

        return view('livewire.admin.orders', ['orders' => $orders]);
    }
}

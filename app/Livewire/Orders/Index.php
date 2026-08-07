<?php

namespace App\Livewire\Orders;

use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.site')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public ?int $category_id = null;

    #[Url]
    public ?int $city_id = null;

    #[Url]
    public string $search = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $orders = Order::query()
            ->where('status', Order::STATUS_OPEN)
            ->when($this->category_id, fn ($q) => $q->where('category_id', $this->category_id))
            ->when($this->city_id, fn ($q) => $q->where('city_id', $this->city_id))
            ->when($this->search, fn ($q) => $q->where('title', 'ilike', "%{$this->search}%"))
            ->with(['category', 'city'])
            ->withCount('offers')
            ->latest()
            ->paginate(12);

        return view('livewire.orders.index', [
            'orders' => $orders,
            'categories' => Category::whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'cities' => City::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

<?php

namespace App\Livewire\Orders;

use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Edit extends Component
{
    public Order $order;

    public ?int $category_id = null;

    public ?int $city_id = null;

    public string $title = '';

    public string $description = '';

    public ?float $budget_min = null;

    public ?float $budget_max = null;

    public string $address = '';

    public function mount(Order $order): void
    {
        abort_unless($order->customer_id === Auth::id(), 403);
        abort_unless(
            in_array($order->status, [Order::STATUS_PENDING_PAYMENT, Order::STATUS_OPEN], true),
            403,
            'Редактировать можно только заказ, который ещё не в работе.'
        );

        $this->order = $order;
        $this->category_id = $order->category_id;
        $this->city_id = $order->city_id;
        $this->title = $order->title;
        $this->description = $order->description;
        $this->budget_min = $order->budget_min;
        $this->budget_max = $order->budget_max;
        $this->address = $order->address ?? '';
    }

    protected function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save()
    {
        abort_unless($this->order->customer_id === Auth::id(), 403);
        abort_unless(
            in_array($this->order->status, [Order::STATUS_PENDING_PAYMENT, Order::STATUS_OPEN], true),
            403
        );

        $data = $this->validate();

        $this->order->update($data);

        return $this->redirect(route('orders.show', $this->order), navigate: true);
    }

    public function render()
    {
        return view('livewire.orders.edit', [
            'categories' => Category::whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'cities' => City::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

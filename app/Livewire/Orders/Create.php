<?php

namespace App\Livewire\Orders;

use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Create extends Component
{
    public ?int $category_id = null;

    public ?int $city_id = null;

    public string $title = '';

    public string $description = '';

    public ?float $budget_min = null;

    public ?float $budget_max = null;

    public string $address = '';

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
        $data = $this->validate();

        $order = Order::create([
            ...$data,
            'customer_id' => Auth::id(),
            'commission_percent' => config('services.platform.commission_percent'),
        ]);

        session()->flash('status', 'Заказ опубликован!');

        return $this->redirect(route('orders.show', $order), navigate: true);
    }

    public function render()
    {
        return view('livewire.orders.create', [
            'categories' => Category::whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'cities' => City::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

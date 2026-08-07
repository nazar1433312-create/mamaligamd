<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Categories extends Component
{
    public string $newName = '';

    public ?int $newParentId = null;

    public function addCategory(): void
    {
        $this->validate(['newName' => ['required', 'string', 'max:100']]);

        Category::create([
            'name' => $this->newName,
            'slug' => Str::slug($this->newName).'-'.Str::random(4),
            'parent_id' => $this->newParentId,
        ]);

        $this->newName = '';
        $this->newParentId = null;
    }

    public function toggleActive(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.categories', [
            'categories' => Category::whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'parents' => Category::whereNull('parent_id')->orderBy('sort_order')->get(),
        ]);
    }
}

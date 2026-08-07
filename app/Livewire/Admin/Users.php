<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.site')]
class Users extends Component
{
    use WithPagination;

    public string $search = '';

    public function toggleBan(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_banned' => ! $user->is_banned]);
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('email', 'ilike', "%{$this->search}%"))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.users', ['users' => $users]);
    }
}

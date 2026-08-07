<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.site')]
class Directory extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = 'rating';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query()
            ->where('is_banned', false)
            ->withCount('completedJobs')
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', "%{$this->search}%"));

        $query = match ($this->sort) {
            'completed' => $query->orderByDesc('completed_jobs_count'),
            'newest' => $query->orderByDesc('created_at'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('rating_avg')->orderByDesc('rating_count'),
        };

        return view('livewire.users.directory', [
            'users' => $query->paginate(15),
        ]);
    }
}

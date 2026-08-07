<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class PublicProfile extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function render()
    {
        $reviews = $this->user->reviewsReceived()->with(['author', 'order'])->latest()->take(20)->get();

        return view('livewire.users.public-profile', [
            'reviews' => $reviews,
        ]);
    }
}

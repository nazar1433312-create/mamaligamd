<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Game extends Component
{
    public function render()
    {
        return view('livewire.game');
    }
}

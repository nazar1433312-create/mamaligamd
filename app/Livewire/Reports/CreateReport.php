<?php

namespace App\Livewire\Reports;

use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use App\Services\Telegram\TelegramApi;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateReport extends Component
{
    public ?Order $order = null;

    public ?User $reportedUser = null;

    public bool $showForm = false;

    public string $reason = '';

    public bool $sent = false;

    public function mount(?Order $order = null, ?User $reportedUser = null): void
    {
        $this->order = $order;
        $this->reportedUser = $reportedUser;
    }

    public function submit(TelegramApi $telegram): void
    {
        $this->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        Report::create([
            'reporter_id' => Auth::id(),
            'reported_user_id' => $this->reportedUser?->id,
            'order_id' => $this->order?->id,
            'reason' => $this->reason,
        ]);

        $chatId = config('services.telegram.admin_chat_id');

        if ($chatId) {
            $target = $this->reportedUser?->name ?? '—';
            $orderInfo = $this->order ? "#{$this->order->id} \"{$this->order->title}\"" : '—';

            $telegram->sendMessage(
                $chatId,
                "🚩 Новая жалоба от ".Auth::user()->name."\nНа пользователя: {$target}\nЗаказ: {$orderInfo}\n\n{$this->reason}"
            );
        }

        $this->reason = '';
        $this->showForm = false;
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.reports.create-report');
    }
}

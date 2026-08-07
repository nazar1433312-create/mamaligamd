<?php

namespace App\Livewire\Admin;

use App\Models\VerificationRequest;
use App\Services\Telegram\TelegramApi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Verifications extends Component
{
    public string $filter = 'pending';

    public function approve(int $requestId, TelegramApi $api): void
    {
        $request = VerificationRequest::findOrFail($requestId);

        $request->update([
            'status' => VerificationRequest::STATUS_APPROVED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $request->user()->update(['is_verified' => true]);

        if ($request->user->telegram_id) {
            $api->sendMessage($request->user->telegram_id, '✅ Ваш аккаунт успешно верифицирован! Теперь доступна оплата картой и у вас есть галочка "Проверено".');
        }
    }

    public function reject(int $requestId, TelegramApi $api): void
    {
        $request = VerificationRequest::findOrFail($requestId);

        $request->update([
            'status' => VerificationRequest::STATUS_REJECTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_comment' => 'Документы нечитаемы или не подходят, попробуйте ещё раз.',
        ]);

        if ($request->user->telegram_id) {
            $api->sendMessage($request->user->telegram_id, '❌ Заявка на верификацию отклонена. Попробуйте загрузить документы ещё раз на сайте.');
        }
    }

    public function render()
    {
        $requests = VerificationRequest::with('user')
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest()
            ->get();

        return view('livewire.admin.verifications', ['requests' => $requests]);
    }
}

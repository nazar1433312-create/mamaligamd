<?php

namespace App\Livewire\Settings;

use App\Models\VerificationRequest;
use App\Services\Telegram\TelegramApi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.site')]
class Verification extends Component
{
    use WithFileUploads;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $documents = [];

    public function submit(TelegramApi $api): void
    {
        abort_if(
            VerificationRequest::where('user_id', Auth::id())->where('status', VerificationRequest::STATUS_PENDING)->exists(),
            403,
            'У вас уже есть заявка на рассмотрении.'
        );

        $this->validate([
            'documents' => ['required', 'array', 'min:1', 'max:3'],
            'documents.*' => ['image', 'max:5120'],
        ]);

        $paths = collect($this->documents)
            ->map(fn ($file) => $file->store('verifications/'.Auth::id(), 'local'))
            ->all();

        $request = VerificationRequest::create([
            'user_id' => Auth::id(),
            'status' => VerificationRequest::STATUS_PENDING,
            'document_paths' => $paths,
        ]);

        $adminChatId = config('services.telegram.admin_chat_id');

        if ($adminChatId) {
            $api->sendMessage(
                $adminChatId,
                "🪪 Новая заявка на верификацию #{$request->id}\nПользователь: {$request->user->name}\nПроверьте в админке: ".rtrim(config('app.url'), '/').'/admin/verifications'
            );
        }

        $this->documents = [];
    }

    public function render()
    {
        return view('livewire.settings.verification', [
            'latest' => VerificationRequest::where('user_id', Auth::id())->latest()->first(),
        ]);
    }
}

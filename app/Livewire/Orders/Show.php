<?php

namespace App\Livewire\Orders;

use App\Models\Message;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Services\LiqPay\PayoutService;
use App\Services\Telegram\UserNotifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Show extends Component
{
    public Order $order;

    public string $offerPrice = '';

    public string $offerMessage = '';

    public string $newMessage = '';

    public string $reviewComment = '';

    public int $reviewRating = 5;

    public function mount(Order $order): void
    {
        $this->order = $order;
    }

    public function makeOffer(UserNotifier $notifier): void
    {
        $this->validate([
            'offerPrice' => ['required', 'numeric', 'min:1'],
            'offerMessage' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_if($this->order->customer_id === Auth::id(), 403, 'Нельзя откликаться на свой заказ.');
        abort_if($this->order->status !== Order::STATUS_OPEN, 403, 'Заказ уже не принимает отклики.');

        Offer::updateOrCreate(
            ['order_id' => $this->order->id, 'executor_id' => Auth::id()],
            ['price' => $this->offerPrice, 'message' => $this->offerMessage, 'status' => Offer::STATUS_PENDING]
        );

        $notifier->notify(
            $this->order->customer,
            "🆕 Новый отклик на заказ #{$this->order->id} \"{$this->order->title}\" от ".Auth::user()->name." — {$this->offerPrice} MDL."
        );

        $this->offerPrice = '';
        $this->offerMessage = '';
        $this->order->refresh();
    }

    public function payCash(UserNotifier $notifier): void
    {
        abort_unless($this->order->customer_id === Auth::id(), 403);
        abort_unless($this->order->status === Order::STATUS_IN_PROGRESS, 403);
        abort_if($this->order->paid_at, 403);

        Payment::create([
            'order_id' => $this->order->id,
            'type' => Payment::TYPE_CHARGE,
            'provider' => 'cash',
            'amount' => $this->order->acceptedOffer->price,
            'status' => Payment::STATUS_SUCCESS,
        ]);

        $this->order->update(['paid_at' => now()]);

        $notifier->notify(
            $this->order->acceptedOffer->executor,
            "💵 Заказчик подтвердил оплату наличными по заказу #{$this->order->id} \"{$this->order->title}\"."
        );

        $this->order->refresh();
    }

    public function markCompleted(PayoutService $payoutService, UserNotifier $notifier): void
    {
        abort_unless($this->order->customer_id === Auth::id(), 403);
        abort_unless($this->order->status === Order::STATUS_IN_PROGRESS, 403);
        abort_unless($this->order->paid_at, 403, 'Сначала оплатите заказ.');

        $this->order->update([
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $wasPaidByCard = Payment::where('order_id', $this->order->id)
            ->where('type', Payment::TYPE_CHARGE)
            ->where('status', Payment::STATUS_SUCCESS)
            ->where('provider', '!=', 'cash')
            ->exists();

        // Cash orders are settled directly between customer and executor —
        // no platform-held funds to pay out, so we skip the payout call.
        if ($wasPaidByCard) {
            $payoutService->payoutForOrder($this->order->fresh(['acceptedOffer.executor']));
        }

        $notifier->notify(
            $this->order->acceptedOffer->executor,
            "🎉 Заказчик подтвердил выполнение заказа #{$this->order->id} \"{$this->order->title}\". Спасибо за работу!"
        );

        $this->order->refresh();
    }

    public function sendMessage(UserNotifier $notifier): void
    {
        $this->validate(['newMessage' => ['required', 'string', 'max:2000']]);

        $recipientId = $this->chatPartnerId();
        abort_unless($recipientId, 403);

        Message::create([
            'order_id' => $this->order->id,
            'sender_id' => Auth::id(),
            'recipient_id' => $recipientId,
            'body' => $this->newMessage,
        ]);

        $notifier->notify(
            \App\Models\User::find($recipientId),
            "💬 Новое сообщение по заказу #{$this->order->id} от ".Auth::user()->name.":\n".mb_substr($this->newMessage, 0, 300)
        );

        $this->newMessage = '';
    }

    public function submitReview(): void
    {
        abort_unless($this->order->status === Order::STATUS_COMPLETED, 403);

        $targetId = $this->chatPartnerId();
        abort_unless($targetId, 403);

        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewComment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::updateOrCreate(
            ['order_id' => $this->order->id, 'author_id' => Auth::id()],
            ['target_id' => $targetId, 'rating' => $this->reviewRating, 'comment' => $this->reviewComment]
        );

        $target = \App\Models\User::find($targetId);
        $target->update([
            'rating_count' => $target->reviewsReceived()->count(),
            'rating_avg' => $target->reviewsReceived()->avg('rating') ?? 0,
        ]);

        $this->reviewComment = '';
    }

    private function chatPartnerId(): ?int
    {
        $userId = Auth::id();

        if ($this->order->customer_id === $userId) {
            return $this->order->acceptedOffer?->executor_id;
        }

        if ($this->order->accepted_offer_id && $this->order->acceptedOffer?->executor_id === $userId) {
            return $this->order->customer_id;
        }

        return null;
    }

    public function render()
    {
        $this->order->load(['category', 'city', 'customer', 'acceptedOffer.executor']);

        $offers = $this->order->offers()->with('executor')->latest()->get();
        $myOffer = $offers->firstWhere('executor_id', Auth::id());

        $messages = $this->chatPartnerId()
            ? $this->order->messages()->with('sender')->orderBy('created_at')->get()
            : collect();

        $myReview = Review::where('order_id', $this->order->id)->where('author_id', Auth::id())->first();

        return view('livewire.orders.show', [
            'offers' => $offers,
            'myOffer' => $myOffer,
            'messages' => $messages,
            'canChat' => (bool) $this->chatPartnerId(),
            'myReview' => $myReview,
            'platformFeeAmount' => config('services.platform.fee_amount'),
        ]);
    }
}

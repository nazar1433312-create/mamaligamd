<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-indigo-600">{{ $order->category->name }}</span>
                <div class="flex items-center gap-2">
                    <span @class([
                        'text-xs font-medium px-2 py-1 rounded-full',
                        'bg-green-100 text-green-700' => $order->status === 'open',
                        'bg-blue-100 text-blue-700' => $order->status === 'in_progress',
                        'bg-gray-100 text-gray-700' => $order->status === 'completed',
                        'bg-red-100 text-red-700' => in_array($order->status, ['cancelled', 'disputed']),
                    ])>{{ __(ucfirst($order->status)) }}</span>
                    @auth
                        <livewire:orders.favorite-button :order="$order" :key="'fav-'.$order->id" />
                        @if (in_array($order->status, ['open', 'pending_payment']) && $order->customer_id === auth()->id())
                            <a href="{{ route('orders.edit', $order) }}" wire:navigate class="text-sm text-gray-400 hover:text-indigo-600">✏️</a>
                        @endif
                    @endauth
                </div>
            </div>

            <h1 class="text-2xl font-bold mb-3">{{ $order->title }}</h1>
            <p class="text-gray-700 whitespace-pre-line mb-4">{{ $order->description }}</p>

            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                <span>📍 {{ $order->city?->name ?? __('Онлайн') }}</span>
                <span>💰
                    @if ($order->budget_min)
                        {{ number_format($order->budget_min, 0, ',', ' ') }}
                        @if ($order->budget_max && $order->budget_max != $order->budget_min)
                            – {{ number_format($order->budget_max, 0, ',', ' ') }}
                        @endif
                        MDL
                    @else
                        {{ __('Договорная') }}
                    @endif
                </span>
                <span>👤 {{ __('Заказчик') }}:
                    <a href="{{ route('users.show', $order->customer) }}" wire:navigate class="text-indigo-600 hover:underline">{{ $order->customer->name }}</a>
                    @if ($order->customer->is_verified) <x-verified-badge /> @endif
                </span>
            </div>

            @auth
                @if ($order->customer_id !== auth()->id())
                    <div class="mt-3">
                        <livewire:reports.create-report :order="$order" :key="'report-order-'.$order->id" />
                    </div>
                @endif
            @endauth
        </div>

        {{-- Pending payment: order not published yet --}}
        @auth
            @if ($order->status === 'pending_payment' && $order->customer_id === auth()->id())
                <div class="bg-amber-50 border border-amber-200 p-6 rounded-xl">
                    <h2 class="font-semibold mb-2">{{ __('Заказ ещё не опубликован') }}</h2>
                    <p class="text-sm text-gray-600 mb-4">{{ __('Комиссия платформы') }}: {{ $platformFeeAmount }} MDL — {{ __('оплатите, чтобы заказ стал виден исполнителям.') }}</p>
                    <a href="{{ route('platform-fee.checkout', $order) }}"
                        class="inline-block bg-indigo-600 text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-indigo-700">
                        💳 {{ __('Оплатить') }} {{ $platformFeeAmount }} MDL {{ __('и опубликовать') }}
                    </a>
                </div>
            @endif
        @endauth

        {{-- Offers --}}
        @if ($order->status === 'open')
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h2 class="font-semibold mb-4">{{ __('Отклики') }} ({{ $offers->count() }})</h2>

                @auth
                    @if ($order->customer_id !== auth()->id())
                        <div class="mb-6 p-4 bg-gray-50 rounded-md">
                            @if ($myOffer)
                                <p class="text-sm text-gray-600">{{ __('Вы откликнулись с ценой') }} <b>{{ $myOffer->price }} MDL</b>. {{ __('Ожидайте решения заказчика.') }}</p>
                            @else
                                <form wire:submit="makeOffer" class="space-y-3">
                                    <div class="flex gap-3">
                                        <input type="number" wire:model="offerPrice" placeholder="{{ __('Ваша цена, MDL') }}" class="rounded-md border-gray-300 text-sm w-40">
                                        <button type="submit" class="bg-indigo-600 text-white px-4 rounded-md text-sm font-medium hover:bg-indigo-700">{{ __('Откликнуться') }}</button>
                                    </div>
                                    @error('offerPrice') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                                    <textarea wire:model="offerMessage" rows="2" placeholder="{{ __('Сообщение заказчику (необязательно)') }}" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                                </form>
                            @endif
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 mb-4"><a href="{{ route('login') }}" class="text-indigo-600 hover:underline">{{ __('Войдите') }}</a>, {{ __('чтобы откликнуться.') }}</p>
                @endauth

                <div class="space-y-3">
                    @forelse ($offers as $offer)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-md">
                            <div>
                                <a href="{{ route('users.show', $offer->executor) }}" wire:navigate class="font-medium text-gray-900 hover:text-indigo-600">{{ $offer->executor->name }}</a>
                                @if ($offer->executor->is_verified) <x-verified-badge /> @endif
                                <x-rating-badge :user="$offer->executor" />
                                <span class="text-sm text-gray-500 ms-2">{{ $offer->price }} MDL</span>
                                @if ($offer->message)
                                    <p class="text-sm text-gray-500 mt-1">{{ $offer->message }}</p>
                                @endif
                            </div>
                            @auth
                                @if ($order->customer_id === auth()->id())
                                    <button wire:click="acceptOffer({{ $offer->id }})" wire:confirm="{{ __('Выбрать этого исполнителя?') }}"
                                        class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-md hover:bg-green-700">{{ __('В работе') }}</button>
                                @endif
                            @endauth
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">{{ __('Пока нет откликов.') }}</p>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- In progress: executor + actions --}}
        @if (in_array($order->status, ['in_progress', 'completed']) && $order->acceptedOffer)
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h2 class="font-semibold mb-2">{{ __('Исполнитель') }}</h2>
                <a href="{{ route('users.show', $order->acceptedOffer->executor) }}" wire:navigate class="text-indigo-600 hover:underline">
                    {{ $order->acceptedOffer->executor->name }}
                </a>
                @if ($order->acceptedOffer->executor->is_verified) <x-verified-badge /> @endif
                <x-rating-badge :user="$order->acceptedOffer->executor" />
                <span class="text-sm text-gray-500 ms-2">{{ $order->acceptedOffer->price }} MDL</span>

                @auth
                    @if ($order->status === 'in_progress' && $order->customer_id === auth()->id())
                        <div class="mt-4">
                            @if (! $order->paid_at)
                                <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Способ оплаты') }}:</p>
                                <div class="flex flex-wrap gap-2">
                                    @if (auth()->user()->is_verified)
                                        <a href="{{ route('payments.liqpay.checkout', $order) }}"
                                            class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                                            💳 {{ __('Картой') }} — {{ $order->acceptedOffer->price }} MDL
                                        </a>
                                    @else
                                        <a href="{{ route('settings.verification') }}" wire:navigate
                                            class="inline-block bg-gray-100 text-gray-500 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-200">
                                            🪪 {{ __('Картой (нужна верификация)') }}
                                        </a>
                                    @endif
                                    <button wire:click="payCash" wire:confirm="{{ __('Подтвердить оплату наличными исполнителю?') }}"
                                        class="inline-block bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50">
                                        💵 {{ __('Наличными') }} — {{ $order->acceptedOffer->price }} MDL
                                    </button>
                                </div>
                            @else
                                <p class="text-sm text-green-700 mb-2">✅ {{ __('Оплачено') }}</p>
                                <button wire:click="markCompleted" wire:confirm="{{ __('Подтвердить, что работа выполнена?') }}"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                                    ✅ {{ __('Работа выполнена') }}
                                </button>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        @endif

        {{-- Review --}}
        @auth
            @if ($order->status === 'completed')
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h2 class="font-semibold mb-4">{{ __('Отзыв') }}</h2>
                    @if ($myReview)
                        <p class="text-sm text-gray-600">{{ __('Вы поставили оценку') }} {{ $myReview->rating }}/5.</p>
                    @else
                        <form wire:submit="submitReview" class="space-y-3">
                            <select wire:model="reviewRating" class="rounded-md border-gray-300 text-sm">
                                @foreach ([5,4,3,2,1] as $r)
                                    <option value="{{ $r }}">{{ $r }} ★</option>
                                @endforeach
                            </select>
                            <textarea wire:model="reviewComment" rows="2" placeholder="{{ __('Комментарий') }}" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">{{ __('Оставить отзыв') }}</button>
                        </form>
                    @endif
                </div>
            @endif
        @endauth
    </div>

    {{-- Chat sidebar --}}
    <div class="lg:col-span-1">
        @if ($canChat)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-[32rem]" wire:poll.5s>
                <div class="p-4 border-b border-gray-200 font-semibold text-sm">{{ __('Чат по заказу') }}</div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    @forelse ($messages as $msg)
                        <div @class(['text-sm', 'text-right' => $msg->sender_id === auth()->id()])>
                            <div @class([
                                'inline-block px-3 py-2 rounded-lg max-w-[85%]',
                                'bg-indigo-600 text-white' => $msg->sender_id === auth()->id(),
                                'bg-gray-100 text-gray-800' => $msg->sender_id !== auth()->id(),
                            ])>
                                {{ $msg->body }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">{{ __('Сообщений пока нет.') }}</p>
                    @endforelse
                </div>
                <form wire:submit="sendMessage" class="p-3 border-t border-gray-200 flex gap-2">
                    <input type="text" wire:model="newMessage" placeholder="{{ __('Написать...') }}" class="flex-1 rounded-md border-gray-300 text-sm">
                    <button type="submit" class="bg-indigo-600 text-white px-3 rounded-md text-sm">➤</button>
                </form>
            </div>
        @endif
    </div>
</div>

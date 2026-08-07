<div class="max-w-xl mx-auto bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
    <h1 class="text-xl font-bold mb-2">{{ __('Поддержка') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ __('Опишите вопрос — мы ответим как можно быстрее.') }}</p>

    @if ($sent)
        <div class="p-4 bg-green-50 text-green-700 rounded-md text-sm">
            {{ __('Спасибо! Мы получили ваше обращение и свяжемся с вами в ближайшее время.') }}
        </div>
    @else
        <form wire:submit="send" class="space-y-4">
            @guest
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Имя') }}</label>
                    <input type="text" wire:model="guestName" class="w-full rounded-md border-gray-300 text-sm">
                    @error('guestName') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Телефон, Telegram или email для связи') }}</label>
                    <input type="text" wire:model="guestContact" class="w-full rounded-md border-gray-300 text-sm">
                    @error('guestContact') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            @endguest

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Тема') }}</label>
                <input type="text" wire:model="subject" class="w-full rounded-md border-gray-300 text-sm">
                @error('subject') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Сообщение') }}</label>
                <textarea wire:model="message" rows="5" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                @error('message') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-md font-medium hover:bg-indigo-700">
                {{ __('Отправить') }}
            </button>
        </form>
    @endif
</div>

<div>
    @if ($sent)
        <p class="text-xs text-green-600">{{ __('Жалоба отправлена. Мы её рассмотрим.') }}</p>
    @elseif ($showForm)
        <form wire:submit="submit" class="space-y-2">
            <textarea wire:model="reason" rows="2" placeholder="{{ __('Опишите проблему') }}" class="w-full rounded-md border-gray-300 text-sm"></textarea>
            @error('reason') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
            <div class="flex gap-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-md hover:bg-red-700 disabled:opacity-50">{{ __('Отправить жалобу') }}</button>
                <button type="button" wire:click="$set('showForm', false)" class="text-xs text-gray-500">{{ __('Отмена') }}</button>
            </div>
        </form>
    @else
        <button wire:click="$set('showForm', true)" class="text-xs text-gray-400 hover:text-red-600">🚩 {{ __('Пожаловаться') }}</button>
    @endif
</div>

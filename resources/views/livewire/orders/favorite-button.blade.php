<button wire:click="toggle" wire:loading.attr="disabled" title="{{ __('В избранное') }}"
    class="text-lg leading-none disabled:opacity-50 {{ $favorited ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400' }}">
    {{ $favorited ? '★' : '☆' }}
</button>

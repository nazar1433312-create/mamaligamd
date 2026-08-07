<button wire:click="toggle" title="{{ __('В избранное') }}"
    class="text-lg leading-none {{ $favorited ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400' }}">
    {{ $favorited ? '★' : '☆' }}
</button>

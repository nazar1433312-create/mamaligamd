@php
    $languages = [
        'en' => 'EN',
        'ro' => 'RO',
        'ru' => 'RU',
        'uk' => 'UA',
        'hi' => 'HI',
    ];
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open" type="button"
        class="flex items-center gap-1 text-sm text-gray-600 hover:text-indigo-600 border border-gray-200 rounded-md px-2.5 py-1.5">
        {{ $languages[app()->getLocale()] ?? 'RO' }}
        <svg class="w-3 h-3" fill="none" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" fill="currentColor" clip-rule="evenodd"/></svg>
    </button>

    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded-md shadow-lg py-1 text-sm">
        @foreach ($languages as $code => $label)
            <a href="{{ route('lang.switch', $code) }}"
               class="block px-3 py-1.5 hover:bg-gray-50 {{ app()->getLocale() === $code ? 'text-indigo-600 font-medium' : 'text-gray-700' }}">
                {{ $label }} — {{ ['en' => 'English', 'ro' => 'Română', 'ru' => 'Русский', 'uk' => 'Українська', 'hi' => 'हिन्दी'][$code] }}
            </a>
        @endforeach
    </div>
</div>

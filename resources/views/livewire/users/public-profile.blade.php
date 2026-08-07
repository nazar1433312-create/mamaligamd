<div class="max-w-3xl mx-auto">
    <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold">{{ $user->name }}</h1>
                <div class="text-sm text-gray-500">
                    ⭐ {{ number_format($user->rating_avg, 1) }} ({{ $user->rating_count }} отзывов)
                    @if ($user->city) · 📍 {{ $user->city->name }} @endif
                </div>
            </div>
        </div>

        @if ($user->about)
            <p class="mt-4 text-gray-700 whitespace-pre-line">{{ $user->about }}</p>
        @endif
    </div>

    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h2 class="font-semibold mb-4">Отзывы</h2>
        <div class="space-y-4">
            @forelse ($reviews as $review)
                <div class="border-b border-gray-100 pb-4 last:border-0">
                    <div class="flex justify-between text-sm mb-1">
                        <a href="{{ route('users.show', $review->author) }}" wire:navigate class="font-medium text-gray-900 hover:text-indigo-600">{{ $review->author->name }}</a>
                        <span>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    </div>
                    @if ($review->comment)
                        <p class="text-sm text-gray-600">{{ $review->comment }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400">Отзывов пока нет.</p>
            @endforelse
        </div>
    </div>
</div>

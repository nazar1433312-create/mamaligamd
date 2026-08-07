@props(['user'])

<span class="inline-flex items-center gap-1 text-xs text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">
    ⭐ {{ number_format($user->rating_avg, 1) }}
    <span class="text-gray-400">({{ $user->rating_count }})</span>
</span>

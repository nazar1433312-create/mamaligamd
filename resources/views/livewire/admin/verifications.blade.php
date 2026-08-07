<div>
    <h1 class="text-2xl font-bold mb-6">Верификации</h1>

    <select wire:model.live="filter" class="rounded-md border-gray-300 text-sm mb-4">
        <option value="pending">Ожидают</option>
        <option value="approved">Одобрены</option>
        <option value="rejected">Отклонены</option>
        <option value="all">Все</option>
    </select>

    <div class="space-y-4">
        @forelse ($requests as $request)
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <a href="{{ route('users.show', $request->user) }}" class="font-medium text-indigo-600 hover:underline">{{ $request->user->name }}</a>
                        <span class="text-xs text-gray-400 ms-2">{{ $request->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <span @class([
                        'text-xs font-medium px-2 py-1 rounded-full',
                        'bg-amber-100 text-amber-700' => $request->status === 'pending',
                        'bg-green-100 text-green-700' => $request->status === 'approved',
                        'bg-red-100 text-red-700' => $request->status === 'rejected',
                    ])>{{ $request->status }}</span>
                </div>

                <div class="flex gap-2 flex-wrap mb-3">
                    @foreach ($request->document_paths as $i => $path)
                        <a href="{{ route('admin.verifications.document', [$request, $i]) }}" target="_blank">
                            <img src="{{ route('admin.verifications.document', [$request, $i]) }}" class="w-24 h-24 object-cover rounded-md border border-gray-200">
                        </a>
                    @endforeach
                </div>

                @if ($request->status === 'pending')
                    <div class="flex gap-2">
                        <button wire:click="approve({{ $request->id }})" wire:confirm="Одобрить верификацию?"
                            class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-md hover:bg-green-700">✅ Одобрить</button>
                        <button wire:click="reject({{ $request->id }})" wire:confirm="Отклонить верификацию?"
                            class="text-sm bg-red-600 text-white px-3 py-1.5 rounded-md hover:bg-red-700">❌ Отклонить</button>
                    </div>
                @elseif ($request->admin_comment)
                    <p class="text-sm text-gray-500">{{ $request->admin_comment }}</p>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400">Заявок нет.</p>
        @endforelse
    </div>
</div>

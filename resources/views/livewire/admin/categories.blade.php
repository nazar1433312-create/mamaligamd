<div>
    <h1 class="text-2xl font-bold mb-6">Категории</h1>

    <form wire:submit="addCategory" class="flex gap-3 mb-6 bg-white p-4 rounded-lg border border-gray-200">
        <input type="text" wire:model="newName" placeholder="Название категории" class="flex-1 rounded-md border-gray-300 text-sm">
        <select wire:model="newParentId" class="rounded-md border-gray-300 text-sm">
            <option value="">— Родительская категория —</option>
            @foreach ($parents as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 rounded-md text-sm font-medium hover:bg-indigo-700">Добавить</button>
    </form>

    <div class="space-y-4">
        @foreach ($categories as $cat)
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="font-semibold {{ $cat->is_active ? '' : 'text-gray-400 line-through' }}">{{ $cat->name }}</span>
                    <button wire:click="toggleActive({{ $cat->id }})" class="text-xs text-indigo-600 hover:underline">
                        {{ $cat->is_active ? 'Скрыть' : 'Показать' }}
                    </button>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($cat->children as $child)
                        <span @class([
                            'text-xs px-2 py-1 rounded-full bg-gray-100',
                            'text-gray-400 line-through' => ! $child->is_active,
                        ])>
                            {{ $child->name }}
                            <button wire:click="toggleActive({{ $child->id }})" class="ms-1 text-indigo-500">✕</button>
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

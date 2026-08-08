<div class="max-w-3xl mx-auto">
    <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
        <h1 class="text-2xl font-bold mb-3">{{ __('О нас') }}</h1>
        <p class="text-gray-700 leading-relaxed">
            {{ __('MamaligaMD — платформа для поиска мастеров и специалистов по всей Молдове. Мы соединяем заказчиков, которым нужна помощь по дому, ремонту, красоте, обучению и не только, с проверенными исполнителями рядом.') }}
        </p>
    </div>

    <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
        <h2 class="text-lg font-semibold mb-5">{{ __('Как это работает') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="flex gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">1</span>
                <div>
                    <h3 class="font-medium">{{ __('Опубликуйте заказ') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Опишите, что нужно сделать, укажите бюджет и город — это займёт пару минут.') }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">2</span>
                <div>
                    <h3 class="font-medium">{{ __('Получите отклики') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Исполнители сами предложат свою цену и условия.') }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">3</span>
                <div>
                    <h3 class="font-medium">{{ __('Выберите исполнителя') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Сравните рейтинг, отзывы и цену — и нажмите «В работе».') }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">4</span>
                <div>
                    <h3 class="font-medium">{{ __('Работа выполнена') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Оплатите картой или наличными и оставьте отзыв.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
        <h2 class="text-lg font-semibold mb-5">{{ __('Почему MamaligaMD') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <h3 class="font-medium mb-1">✓ {{ __('Верификация') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Исполнители могут пройти проверку документов и получить галочку «Проверено».') }}</p>
            </div>
            <div>
                <h3 class="font-medium mb-1">⭐ {{ __('Рейтинги и отзывы') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Реальные оценки от заказчиков помогают выбрать лучшего специалиста.') }}</p>
            </div>
            <div>
                <h3 class="font-medium mb-1">🇲🇩 {{ __('Только Молдова') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Города, категории и оплата — всё заточено под молдавский рынок.') }}</p>
            </div>
            <div>
                <h3 class="font-medium mb-1">💬 {{ __('Общение внутри платформы') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Чат и звонки прямо на сайте, без обмена личными номерами.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm text-center">
        <h2 class="text-lg font-semibold mb-2">{{ __('Остались вопросы?') }}</h2>
        <a href="{{ route('support.create') }}" wire:navigate class="inline-block bg-indigo-600 text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-indigo-700">
            {{ __('Написать в поддержку') }}
        </a>
    </div>
</div>

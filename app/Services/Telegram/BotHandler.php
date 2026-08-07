<?php

namespace App\Services\Telegram;

use App\Models\Category;
use App\Models\City;
use App\Models\Offer;
use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\TelegramLoginToken;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BotHandler
{
    private const STATE_TTL = 1800; // 30 minutes

    public function __construct(
        private readonly TelegramApi $api
    ) {}

    private function sendPaymentLink(User $user, int $chatId, Order $order): void
    {
        $loginToken = TelegramLoginToken::issueFor($user);
        $url = rtrim(config('app.url'), '/')."/auth/telegram/{$loginToken->token}?to=".rawurlencode("/orders/{$order->id}/start");

        $this->api->sendMessage(
            $chatId,
            "Осталось оплатить комиссию платформы за заказ «{$order->title}»: ".config('services.platform.fee_amount')." MDL, чтобы заказ #{$order->id} стал виден исполнителям.",
            ['inline_keyboard' => [[['text' => '💳 Оплатить и опубликовать', 'url' => $url]]]]
        );
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }
    }

    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $from = $message['from'];
        $text = trim($message['text'] ?? '');

        if ($this->handleAdminSupportReply($chatId, $message, $text)) {
            return;
        }

        $user = $this->findOrCreateUser($from, $chatId);

        if ($text === '/start') {
            $this->clearState($chatId);
            $this->sendWelcome($user, $chatId);

            return;
        }

        if ($text === BtnLabels::CANCEL) {
            $this->clearState($chatId);
            $this->api->sendMessage($chatId, 'Действие отменено.', $this->mainMenu());

            return;
        }

        if ($text === BtnLabels::NEW_ORDER) {
            $this->startOrderCreation($chatId);

            return;
        }

        if ($text === BtnLabels::MY_ORDERS) {
            $this->showMyOrders($user, $chatId);

            return;
        }

        if ($text === BtnLabels::BROWSE) {
            $this->showBrowseOrders($chatId);

            return;
        }

        if ($text === BtnLabels::SITE) {
            $this->sendSiteLink($user, $chatId);

            return;
        }

        $state = $this->getState($chatId);

        if ($state) {
            $this->handleConversationInput($user, $chatId, $state, $text);

            return;
        }

        $this->api->sendMessage($chatId, 'Не понял команду. Выберите пункт меню ниже 👇', $this->mainMenu());
    }

    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'] ?? '';
        $from = $callbackQuery['from'];

        $user = $this->findOrCreateUser($from, $chatId);
        $this->api->answerCallbackQuery($callbackQuery['id']);

        if (str_starts_with($data, 'cat:')) {
            $this->onCategoryChosen($chatId, $messageId, (int) substr($data, 4));

            return;
        }

        if (str_starts_with($data, 'city:')) {
            $this->onCityChosen($chatId, $messageId, (int) substr($data, 5));

            return;
        }

        if ($data === 'confirm_publish') {
            $this->publishOrder($user, $chatId, $messageId);

            return;
        }

        if ($data === 'cancel_order') {
            $this->clearState($chatId);
            $this->api->editMessageText($chatId, $messageId, 'Черновик заказа отменён.');

            return;
        }

        if (str_starts_with($data, 'offer:')) {
            $this->onOrderOfferRequest($user, $chatId, (int) substr($data, 6));

            return;
        }
    }

    // --- Support ticket replies from the admin ------------------------------------

    /**
     * If this message comes from the admin chat and is a reply to one of our
     * ticket notifications, treat it as the admin's answer to that ticket.
     */
    private function handleAdminSupportReply(int $chatId, array $message, string $text): bool
    {
        $adminChatId = config('services.telegram.admin_chat_id');
        $replyTo = $message['reply_to_message']['message_id'] ?? null;

        if (! $adminChatId || (string) $chatId !== (string) $adminChatId || ! $replyTo || $text === '') {
            return false;
        }

        $ticket = SupportTicket::where('telegram_notify_chat_id', $chatId)
            ->where('telegram_notify_message_id', $replyTo)
            ->first();

        if (! $ticket) {
            return false;
        }

        $ticket->messages()->create([
            'sender_type' => SupportMessage::SENDER_ADMIN,
            'body' => $text,
        ]);

        $ticket->update(['status' => SupportTicket::STATUS_OPEN]);

        if ($ticket->user?->telegram_id) {
            $this->api->sendMessage(
                $ticket->user->telegram_id,
                "💬 Ответ по вашему обращению #{$ticket->id} \"{$ticket->subject}\":\n\n{$text}"
            );
        }

        $this->api->sendMessage($chatId, "✅ Ответ отправлен пользователю по обращению #{$ticket->id}.");

        return true;
    }

    // --- Registration / menu ---------------------------------------------------

    private function findOrCreateUser(array $from, int $chatId): User
    {
        $user = User::where('telegram_id', $chatId)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name' => trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? '')) ?: ('tg_'.$chatId),
            'telegram_id' => $chatId,
            'telegram_username' => $from['username'] ?? null,
        ]);
    }

    private function sendWelcome(User $user, int $chatId): void
    {
        $this->api->sendMessage(
            $chatId,
            "Привет, {$user->name}! 👋\n\nЭто бот сервиса MamaligaMD — здесь можно опубликовать заказ на услугу или найти работу.",
            $this->mainMenu()
        );
    }

    private function mainMenu(): array
    {
        return [
            'keyboard' => [
                [BtnLabels::NEW_ORDER],
                [BtnLabels::MY_ORDERS, BtnLabels::BROWSE],
                [BtnLabels::SITE],
            ],
            'resize_keyboard' => true,
        ];
    }

    private function cancelKeyboard(): array
    {
        return [
            'keyboard' => [[BtnLabels::CANCEL]],
            'resize_keyboard' => true,
        ];
    }

    private function sendSiteLink(User $user, int $chatId): void
    {
        $token = TelegramLoginToken::issueFor($user);
        $url = rtrim(config('app.url'), '/')."/auth/telegram/{$token->token}";

        $this->api->sendMessage($chatId, "Открыть сайт с автоматическим входом (ссылка на 2 минуты):\n{$url}");
    }

    // --- Order creation conversation --------------------------------------------

    private function startOrderCreation(int $chatId): void
    {
        $this->setState($chatId, ['step' => 'category', 'data' => []]);

        $categories = Category::whereNull('parent_id')->orderBy('sort_order')->get();

        $this->api->sendMessage($chatId, 'В какой категории нужна услуга?', [
            'inline_keyboard' => $this->categoryButtons($categories),
        ]);
    }

    private function categoryButtons($categories): array
    {
        return $categories->map(fn ($c) => [['text' => $c->name, 'callback_data' => "cat:{$c->id}"]])->toArray();
    }

    private function onCategoryChosen(int $chatId, int $messageId, int $categoryId): void
    {
        $category = Category::find($categoryId);

        if (! $category) {
            return;
        }

        $children = $category->children;

        if ($children->isNotEmpty()) {
            $this->api->editMessageText($chatId, $messageId, "Категория: {$category->name}\nУточните подкатегорию:", [
                'inline_keyboard' => $this->categoryButtons($children),
            ]);

            return;
        }

        $state = $this->getState($chatId) ?? ['step' => 'category', 'data' => []];
        $state['data']['category_id'] = $category->id;
        $state['step'] = 'title';
        $this->setState($chatId, $state);

        $this->api->editMessageText($chatId, $messageId, "Категория: {$category->name} ✅");
        $this->api->sendMessage($chatId, 'Кратко опишите, что нужно сделать (заголовок заказа):', $this->cancelKeyboard());
    }

    private function handleConversationInput(User $user, int $chatId, array $state, string $text): void
    {
        switch ($state['step']) {
            case 'title':
                $state['data']['title'] = mb_substr($text, 0, 120);
                $state['step'] = 'description';
                $this->setState($chatId, $state);
                $this->api->sendMessage($chatId, 'Опишите подробнее, что нужно сделать:', $this->cancelKeyboard());
                break;

            case 'description':
                $state['data']['description'] = mb_substr($text, 0, 2000);
                $state['step'] = 'budget';
                $this->setState($chatId, $state);
                $this->api->sendMessage($chatId, 'Какой бюджет? Укажите сумму в MDL (или "договорная"):', $this->cancelKeyboard());
                break;

            case 'budget':
                $state['data']['budget'] = $text;
                $state['step'] = 'city';
                $this->setState($chatId, $state);
                $cities = City::where('is_active', true)->orderBy('name')->get();
                $this->api->sendMessage($chatId, 'В каком городе нужна услуга?', [
                    'inline_keyboard' => $cities->map(fn ($c) => [['text' => $c->name, 'callback_data' => "city:{$c->id}"]])->toArray(),
                ]);
                break;

            default:
                $this->api->sendMessage($chatId, 'Пожалуйста, выберите вариант из клавиатуры.', $this->cancelKeyboard());
        }
    }

    private function onCityChosen(int $chatId, int $messageId, int $cityId): void
    {
        $city = City::find($cityId);
        $state = $this->getState($chatId);

        if (! $city || ! $state) {
            return;
        }

        $state['data']['city_id'] = $city->id;
        $state['step'] = 'confirm';
        $this->setState($chatId, $state);

        $d = $state['data'];
        $category = Category::find($d['category_id']);

        $summary = "<b>Проверьте заказ:</b>\n\n"
            ."Категория: {$category->name}\n"
            ."Город: {$city->name}\n"
            ."Заголовок: {$d['title']}\n"
            ."Описание: {$d['description']}\n"
            ."Бюджет: {$d['budget']}\n";

        $this->api->editMessageText($chatId, $messageId, "Город: {$city->name} ✅");
        $this->api->sendMessage($chatId, $summary, [
            'inline_keyboard' => [
                [['text' => '✅ Опубликовать', 'callback_data' => 'confirm_publish']],
                [['text' => '❌ Отмена', 'callback_data' => 'cancel_order']],
            ],
        ]);
    }

    private function publishOrder(User $user, int $chatId, int $messageId): void
    {
        $state = $this->getState($chatId);

        if (! $state || $state['step'] !== 'confirm') {
            return;
        }

        $d = $state['data'];

        [$budgetMin, $budgetMax] = $this->parseBudget($d['budget']);

        $order = Order::create([
            'customer_id' => $user->id,
            'category_id' => $d['category_id'],
            'city_id' => $d['city_id'],
            'title' => $d['title'],
            'description' => $d['description'],
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        $this->clearState($chatId);

        $this->api->editMessageText($chatId, $messageId, "Заказ #{$order->id} создан, осталось оплатить публикацию.");
        $this->sendPaymentLink($user, $chatId, $order);
    }

    private function parseBudget(string $text): array
    {
        if (preg_match('/(\d+)\s*-\s*(\d+)/', $text, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        if (preg_match('/\d+/', $text, $m)) {
            return [(float) $m[0], (float) $m[0]];
        }

        return [null, null];
    }

    // --- Browsing / my orders ----------------------------------------------------

    private function showMyOrders(User $user, int $chatId): void
    {
        $orders = $user->orders()->latest()->take(5)->get();

        if ($orders->isEmpty()) {
            $this->api->sendMessage($chatId, 'У вас пока нет заказов.', $this->mainMenu());

            return;
        }

        foreach ($orders as $order) {
            $offersCount = $order->offers()->count();
            $this->api->sendMessage(
                $chatId,
                "<b>#{$order->id} {$order->title}</b>\nСтатус: {$order->status}\nОткликов: {$offersCount}"
            );
        }
    }

    private function showBrowseOrders(int $chatId): void
    {
        $orders = Order::where('status', Order::STATUS_OPEN)->latest()->take(10)->get();

        if ($orders->isEmpty()) {
            $this->api->sendMessage($chatId, 'Пока нет открытых заказов.', $this->mainMenu());

            return;
        }

        foreach ($orders as $order) {
            $this->api->sendMessage(
                $chatId,
                "<b>#{$order->id} {$order->title}</b>\n{$order->category->name}, {$order->city?->name}\n".
                    mb_substr($order->description, 0, 200),
                ['inline_keyboard' => [[['text' => '💬 Откликнуться на сайте', 'callback_data' => "offer:{$order->id}"]]]]
            );
        }
    }

    private function onOrderOfferRequest(User $user, int $chatId, int $orderId): void
    {
        $token = TelegramLoginToken::issueFor($user);
        $url = rtrim(config('app.url'), '/')."/auth/telegram/{$token->token}?redirect=/orders/{$orderId}";

        $this->api->sendMessage($chatId, "Чтобы откликнуться, перейдите на сайт:\n{$url}");
    }

    // --- Conversation state (Redis-backed cache) ----------------------------------

    private function stateKey(int $chatId): string
    {
        return "tg:state:{$chatId}";
    }

    private function getState(int $chatId): ?array
    {
        return Cache::get($this->stateKey($chatId));
    }

    private function setState(int $chatId, array $state): void
    {
        Cache::put($this->stateKey($chatId), $state, self::STATE_TTL);
    }

    private function clearState(int $chatId): void
    {
        Cache::forget($this->stateKey($chatId));
    }
}

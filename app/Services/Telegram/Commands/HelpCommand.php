<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class HelpCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        Log::info('Запрос справки через Telegram');

        $message = "❓ <b>СПРАВКА ПО КОМАНДАМ</b>\n\n";

        $message .= "🤖 <b>Основные команды:</b>\n";
        $message .= "/start - Главное меню и приветствие\n";
        $message .= "/run - Запустить анализ\n";
        $message .= "/status - Показать статус системы\n";
        $message .= "/settings - Текущие настройки системы\n";
        $message .= "/help - Эта справка\n\n";

        $message .= "🔘 <b>Интерактивные кнопки:</b>\n";
        $message .= "• Используйте кнопки под сообщениями\n";
        $message .= "• Быстрый доступ к основным функциям\n";
        $message .= "• Автоматический возврат в меню\n\n";

        $message .= "⏰ Обновлено: " . now()->format('H:i:s');

        return [
            'text' => $message,
            'buttons' => KeyboardBuilder::backToMenu(),
        ];
    }
}

<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class StatusCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        Log::info('Запрос статуса системы через Telegram');

        try {
            $message = "📊 <b>СТАТУС СИСТЕМЫ</b>\n\n";

            // Статус системы
            $message .= "⚙️ <b>Система:</b>\n";
            $message .= "• Статус: 🟢 Работает\n";
            $message .= "• Время: " . now()->format('H:i:s') . "\n\n";
            $message .= "🚀 <b>Арбитраж:</b>\n";
            $message .= "• Анализ выполняется через Python скрипт\n";

            return [
                'text' => $message,
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка получения статуса системы', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'text' => "❌ <b>Ошибка получения статуса</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        }
    }
}

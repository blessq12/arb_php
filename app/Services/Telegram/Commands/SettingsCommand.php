<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class SettingsCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        Log::info('Запрос настроек через Telegram');

        try {
            $message = "⚙️ <b>НАСТРОЙКИ СИСТЕМЫ</b>\n\n";

            $message .= "📋 <b>Текущие настройки:</b>\n";
            $message .= "• Telegram настроен: " . ($this->telegramService->isConfigured() ? '✅ Да' : '❌ Нет') . "\n";
            $message .= "• Время: " . now()->format('H:i:s') . "\n";

            return [
                'text' => $message,
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка получения настроек', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'text' => "❌ <b>Ошибка получения настроек</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        }
    }
}

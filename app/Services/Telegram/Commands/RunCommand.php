<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class RunCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        $chatId = $data['chat_id'];

        Log::info('Запуск анализа через Telegram команду', ['chat_id' => $chatId]);

        try {
            $message = "✅ <b>Анализ запущен!</b>\n\n";
            $message .= "🎯 <b>Детали запуска:</b>\n";
            $message .= "• Статус: Запущен\n";
            $message .= "• Время запуска: " . now()->format('H:i:s') . "\n\n";
            $message .= "📊 <b>Результаты:</b> Придут автоматически\n";

            return [
                'text' => $message,
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        } catch (\Exception $e) {
            Log::error('Исключение при выполнении команды /run', [
                'exception' => $e->getMessage(),
                'chat_id' => $chatId,
            ]);

            return [
                'text' => "❌ <b>Ошибка запуска</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        }
    }
}

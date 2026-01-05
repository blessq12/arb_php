<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class WorkerCleanupCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        Log::info('Очистка очередей через Telegram');

        try {
            $failedCount = \DB::table('failed_jobs')->count();
            \DB::table('failed_jobs')->truncate();

            $message = "🧹 <b>ОЧИСТКА ОЧЕРЕДЕЙ</b>\n\n";
            $message .= "• Удалено неудачных задач: {$failedCount}\n";
            $message .= "• Время: " . now()->format('H:i:s') . "\n";

            return [
                'text' => $message,
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка очистки очередей', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'text' => "❌ <b>Ошибка очистки очередей</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        }
    }
}

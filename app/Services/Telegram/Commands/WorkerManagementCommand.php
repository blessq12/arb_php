<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class WorkerManagementCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        Log::info('Запрос статуса воркеров через Telegram');

        try {
            $message = "👷 <b>УПРАВЛЕНИЕ ВОРКЕРАМИ</b>\n\n";

            $queueJobs = \DB::table('jobs')->count();
            $failedJobs = \DB::table('failed_jobs')->count();

            $message .= "📊 <b>Статус:</b>\n";
            $message .= "• Задач в очереди: {$queueJobs}\n";
            $message .= "• Неудачных задач: {$failedJobs}\n\n";

            $message .= "⏰ Обновлено: " . now()->format('H:i:s');

            return [
                'text' => $message,
                'buttons' => KeyboardBuilder::workerManagementMenu(),
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка получения статуса воркеров', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'text' => "❌ <b>Ошибка получения статуса воркеров</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu(),
            ];
        }
    }
}

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
            // Получаем настройки из конфига
            $pythonPath = config('services.python.path');
            $scriptPath = config('services.python.script_path');
            
            // Запускаем Python скрипт в фоне
            $command = sprintf(
                'cd %s && %s %s > /dev/null 2>&1 &',
                escapeshellarg(dirname($scriptPath)),
                escapeshellarg($pythonPath),
                escapeshellarg($scriptPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('Не удалось запустить Python скрипт');
            }

            $message = "✅ <b>Анализ запущен!</b>\n\n";
            $message .= "🎯 <b>Детали запуска:</b>\n";
            $message .= "• Статус: Запущен\n";
            $message .= "• Время запуска: " . now()->format('H:i:s') . "\n\n";
            $message .= "📊 <b>Результаты:</b> Придут автоматически\n";

            Log::info('Python скрипт успешно запущен', [
                'command' => $command,
                'chat_id' => $chatId,
            ]);

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

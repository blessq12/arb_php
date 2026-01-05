<?php

namespace App\Services\Telegram;

use App\Services\TelegramService;
use App\Services\Telegram\Commands;
use App\Services\Telegram\KeyboardBuilder;
use Illuminate\Support\Facades\Log;

class CallbackHandler
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обрабатывает callback query
     */
    public function handle(array $callbackQuery): array
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $callbackData = $callbackQuery['data'];
        $callbackQueryId = $callbackQuery['id'];

        Log::info('Обработка callback query', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'callback_data' => $callbackData
        ]);

        try {
            // Отвечаем на callback query чтобы убрать "часики"
            $this->telegramService->answerCallbackQuery($callbackQueryId);

            // Обрабатываем callback data
            $result = $this->processCallbackData($callbackData, $chatId, $messageId);

            if ($result) {
                // Редактируем сообщение
                $this->telegramService->editMessageText(
                    $chatId,
                    $messageId,
                    $result['text'],
                    $result['buttons'] ?? null
                );
            }

            return [
                'success' => true,
                'processed' => true
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка обработки callback query', [
                'exception' => $e->getMessage(),
                'callback_data' => $callbackData,
                'chat_id' => $chatId
            ]);

            // Отправляем сообщение об ошибке
            $this->telegramService->sendMessageToChat($chatId, "❌ Ошибка: " . $e->getMessage());

            return [
                'success' => false,
                'processed' => true
            ];
        }
    }

    /**
     * Обрабатывает callback data
     */
    private function processCallbackData(string $callbackData, string $chatId, int $messageId): ?array
    {
        $parts = explode('_', $callbackData);
        $action = $parts[0];

        switch ($action) {
            case 'start':
            case 'menu':
                return $this->handleStartCommand();

            case 'run':
                return $this->handleRunCommand($chatId);

            case 'status':
                return $this->handleStatusCommand();

            case 'settings':
                return $this->handleSettingsCommand();

            case 'help':
                return $this->handleHelpCommand();

            case 'back':
                return $this->handleBackCallback($parts, $chatId, $messageId);

            default:
                Log::warning('Неизвестный callback action', ['action' => $action]);
                return null;
        }
    }

    /**
     * Обрабатывает callback для возврата
     */
    private function handleBackCallback(array $parts, string $chatId, int $messageId): ?array
    {
        // back_to_menu разбивается на ['back', 'to', 'menu']
        if (count($parts) >= 3 && $parts[1] === 'to' && $parts[2] === 'menu') {
            return $this->getMainMenu();
        }

        return null;
    }

    /**
     * Обработчики основных команд
     */
    private function handleStartCommand(): array
    {
        try {
            $startCommand = new Commands\StartCommand($this->telegramService);
            $result = $startCommand->handle([]);
            return $result;
        } catch (\Exception $e) {
            return [
                'text' => "❌ <b>Ошибка</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::mainMenu()
            ];
        }
    }

    private function handleRunCommand(string $chatId): array
    {
        try {
            $runCommand = new Commands\RunCommand($this->telegramService);
            $result = $runCommand->handle(['chat_id' => $chatId]);
            return $result;
        } catch (\Exception $e) {
            return [
                'text' => "❌ <b>Ошибка запуска анализа</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu()
            ];
        }
    }

    private function handleStatusCommand(): array
    {
        try {
            $statusCommand = new Commands\StatusCommand($this->telegramService);
            $result = $statusCommand->handle([]);
            return $result;
        } catch (\Exception $e) {
            return [
                'text' => "❌ <b>Ошибка получения статуса</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu()
            ];
        }
    }

    private function handleSettingsCommand(): array
    {
        try {
            $settingsCommand = new Commands\SettingsCommand($this->telegramService);
            $result = $settingsCommand->handle([]);
            return $result;
        } catch (\Exception $e) {
            return [
                'text' => "❌ <b>Ошибка получения настроек</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu()
            ];
        }
    }

    private function handleHelpCommand(): array
    {
        try {
            $helpCommand = new Commands\HelpCommand($this->telegramService);
            $result = $helpCommand->handle([]);
            return $result;
        } catch (\Exception $e) {
            return [
                'text' => "❌ <b>Ошибка получения справки</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::backToMenu()
            ];
        }
    }

    private function getMainMenu(): array
    {
        try {
            $startCommand = new Commands\StartCommand($this->telegramService);
            $result = $startCommand->handle([]);
            return $result;
        } catch (\Exception $e) {
            return [
                'text' => "❌ <b>Ошибка</b>\n\n{$e->getMessage()}",
                'buttons' => KeyboardBuilder::mainMenu()
            ];
        }
    }
}

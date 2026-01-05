<?php

namespace App\Services\Telegram;

use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    private TelegramService $telegramService;
    private CallbackHandler $callbackHandler;
    private array $commands = [];

    public function __construct(TelegramService $telegramService, CallbackHandler $callbackHandler)
    {
        $this->telegramService = $telegramService;
        $this->callbackHandler = $callbackHandler;
        $this->registerCommands();
    }

    /**
     * Главный обработчик входящих обновлений
     */
    public function handleUpdate(array $update): void
    {
        try {
            // Обрабатываем message (текст/команды)
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            }

            // Обрабатываем callback_query (нажатие кнопок)
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка обработки обновления Telegram', [
                'exception' => $e->getMessage(),
                'update' => $update,
            ]);

            // Отправляем ошибку в чат если есть chat_id
            $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null;
            if ($chatId) {
                $this->telegramService->sendMessageToChat($chatId, "❌ Ошибка обработки команды: " . $e->getMessage());
            }
        }
    }

    /**
     * Обработка входящих сообщений (команды и текст)
     */
    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $messageId = $message['message_id'] ?? null;

        Log::info('Обработка сообщения Telegram', [
            'chat_id' => $chatId,
            'text' => $text,
            'message_id' => $messageId,
        ]);

        // Определяем тип команды
        $command = $this->parseCommand($text);

        if ($command) {
            $this->executeCommand($command, [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'message' => $message,
            ]);
        } else {
            // Неизвестная команда
            $this->telegramService->sendMessageToChat($chatId, "❓ Неизвестная команда. Используйте /help для списка команд.");
        }
    }

    /**
     * Обработка callback_query (нажатие inline кнопок)
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        try {
            $result = $this->callbackHandler->handle($callbackQuery);

            if (!$result['processed']) {
                // Если callback не был обработан, пробуем выполнить как команду
                $callbackData = $callbackQuery['data'] ?? '';
                $chatId = $callbackQuery['message']['chat']['id'];
                $messageId = $callbackQuery['message']['message_id'] ?? null;

                $this->executeCommand($callbackData, [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'callback_query' => $callbackQuery,
                    'is_callback' => true,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка обработки callback_query', [
                'exception' => $e->getMessage(),
                'callback_query' => $callbackQuery,
            ]);

            $chatId = $callbackQuery['message']['chat']['id'];
            $this->telegramService->sendMessageToChat($chatId, "❌ Ошибка обработки кнопки: " . $e->getMessage());
        }
    }

    /**
     * Парсинг команды из текста
     */
    private function parseCommand(string $text): ?string
    {
        $text = trim($text);

        // Команды начинающиеся с /
        if (str_starts_with($text, '/')) {
            return explode(' ', $text)[0];
        }

        return null;
    }

    /**
     * Выполнение команды
     */
    private function executeCommand(string $command, array $data): void
    {
        if (!isset($this->commands[$command])) {
            $this->telegramService->sendMessageToChat($data['chat_id'], "❓ Команда '{$command}' не найдена. Используйте /help для списка команд.");
            return;
        }

        $commandClass = $this->commands[$command];
        $commandInstance = new $commandClass($this->telegramService);

        try {
            $result = $commandInstance->handle($data);

            if (isset($result['text'])) {
                // Если это callback query, редактируем существующее сообщение
                if (isset($data['is_callback']) && $data['is_callback'] && isset($data['message_id'])) {
                    $this->telegramService->editMessageText(
                        $data['chat_id'],
                        $data['message_id'],
                        $result['text'],
                        $result['buttons'] ?? null
                    );
                } else {
                    // Иначе отправляем новое сообщение
                    if (isset($result['buttons']) && !empty($result['buttons'])) {
                        $this->telegramService->sendMessageWithInlineKeyboard(
                            $data['chat_id'],
                            $result['text'],
                            $result['buttons']
                        );
                    } else {
                        $this->telegramService->sendMessageToChat($data['chat_id'], $result['text']);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Ошибка выполнения команды {$command}", [
                'exception' => $e->getMessage(),
                'data' => $data,
            ]);

            $this->telegramService->sendMessageToChat($data['chat_id'], "❌ Ошибка выполнения команды: " . $e->getMessage());
        }
    }

    /**
     * Регистрация команд
     */
    private function registerCommands(): void
    {
        $this->commands = [
            // Основные команды
            '/start' => Commands\StartCommand::class,
            '/run' => Commands\RunCommand::class,
            '/status' => Commands\StatusCommand::class,
            '/settings' => Commands\SettingsCommand::class,
            '/help' => Commands\HelpCommand::class,

            // Новые команды (упрощено)
            '/worker' => Commands\WorkerManagementCommand::class,
            '/worker_restart' => Commands\WorkerRestartCommand::class,
            '/worker_cleanup' => Commands\WorkerCleanupCommand::class,

            // Callback data для кнопок
            'start' => Commands\StartCommand::class,
            'menu' => Commands\StartCommand::class,
            'run' => Commands\RunCommand::class,
            'status' => Commands\StatusCommand::class,
            'worker' => Commands\WorkerManagementCommand::class,
            'worker_restart' => Commands\WorkerRestartCommand::class,
            'worker_cleanup' => Commands\WorkerCleanupCommand::class,
            'settings' => Commands\SettingsCommand::class,
            'help' => Commands\HelpCommand::class,
        ];
    }
}

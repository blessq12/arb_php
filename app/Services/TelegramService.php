<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $chatId;
    private string $messageTemplate;

    public function __construct()
    {
        // Приоритет: .env файл -> конфиг
        $this->botToken = env('TELEGRAM_BOT_TOKEN', config('services.telegram.bot_token', ''));
        $this->chatId = env('TELEGRAM_CHAT_ID', config('services.telegram.chat_id', ''));
        $this->messageTemplate = config('services.telegram.message_template', 'Пара {pair}: профит {profit}% на {exchange}');
    }

    /**
     * Отправляет сообщение в Telegram
     */
    public function sendMessage(string $message): bool
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::warning('Telegram не настроен: отсутствует токен или ID чата');
            return false;
        }

        try {
            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                Log::info('Сообщение отправлено в Telegram', ['message' => $message]);
                return true;
            } else {
                Log::error('Ошибка отправки в Telegram', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'message' => $message
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Исключение при отправке в Telegram', [
                'exception' => $e->getMessage(),
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Отправляет сообщение в указанный чат
     */
    public function sendMessageToChat(string $chatId, string $message): bool
    {
        if (empty($this->botToken)) {
            Log::warning('Telegram не настроен: отсутствует токен');
            return false;
        }

        try {
            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                Log::info('Сообщение отправлено в Telegram', [
                    'chat_id' => $chatId,
                    'message_length' => strlen($message)
                ]);
                return true;
            } else {
                Log::error('Ошибка отправки в Telegram', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Исключение при отправке в Telegram', [
                'exception' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return false;
        }
    }

    /**
     * Отправляет сообщение с inline клавиатурой
     */
    public function sendMessageWithInlineKeyboard(string $chatId, string $message, array $keyboard): bool
    {
        if (empty($this->botToken)) {
            Log::warning('Telegram не настроен: отсутствует токен');
            return false;
        }

        try {
            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode($keyboard),
            ]);

            if ($response->successful()) {
                Log::info('Сообщение с клавиатурой отправлено в Telegram', [
                    'chat_id' => $chatId,
                    'message_length' => strlen($message)
                ]);
                return true;
            } else {
                Log::error('Ошибка отправки сообщения с клавиатурой в Telegram', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Исключение при отправке сообщения с клавиатурой в Telegram', [
                'exception' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return false;
        }
    }

    /**
     * Ответ на callback query (убирает "часики" у кнопки)
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): bool
    {
        if (empty($this->botToken)) {
            Log::warning('Telegram не настроен: отсутствует токен');
            return false;
        }

        try {
            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
            ]);

            if ($response->successful()) {
                Log::info('Callback query ответ отправлен', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => $text
                ]);
                return true;
            } else {
                Log::error('Ошибка ответа на callback query', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'callback_query_id' => $callbackQueryId
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Исключение при ответе на callback query', [
                'exception' => $e->getMessage(),
                'callback_query_id' => $callbackQueryId
            ]);
            return false;
        }
    }

    /**
     * Редактирует текст сообщения
     */
    public function editMessageText(string $chatId, int $messageId, string $text, ?array $keyboard = null): bool
    {
        if (empty($this->botToken)) {
            Log::warning('Telegram не настроен: отсутствует токен');
            return false;
        }

        try {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if ($keyboard) {
                $data['reply_markup'] = json_encode($keyboard);
            }

            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$this->botToken}/editMessageText", $data);

            if ($response->successful()) {
                Log::info('Сообщение отредактировано в Telegram', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId
                ]);
                return true;
            } else {
                Log::error('Ошибка редактирования сообщения в Telegram', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId,
                    'message_id' => $messageId
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Исключение при редактировании сообщения в Telegram', [
                'exception' => $e->getMessage(),
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
            return false;
        }
    }

    /**
     * Проверяет настройки Telegram
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }
}

<?php

namespace App\Services\Telegram\Commands;

interface TelegramCommandInterface
{
    /**
     * Обработка команды
     *
     * @param array $data Данные от Telegram (chat_id, message, callback_query и т.д.)
     * @return array ['text' => string, 'buttons' => array|null]
     */
    public function handle(array $data): array;
}

<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\KeyboardBuilder;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class StartCommand implements TelegramCommandInterface
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(array $data): array
    {
        Log::info('Пользователь запустил бота через /start');

        $message = "🤖 <b>Добро пожаловать в бот арбитража!</b>\n\n";
        $message .= "Этот бот поможет вам:\n";
        $message .= "🚀 Запускать анализ арбитража по требованию\n";
        $message .= "📊 Отслеживать статус системы\n\n";
        $message .= "Выберите действие из меню ниже:";

        return [
            'text' => $message,
            'buttons' => KeyboardBuilder::mainMenu(),
        ];
    }
}

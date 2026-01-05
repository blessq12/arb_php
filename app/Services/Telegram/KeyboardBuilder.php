<?php

namespace App\Services\Telegram;

class KeyboardBuilder
{
    /**
     * Создает inline клавиатуру с кнопками
     */
    public static function inlineKeyboard(array $buttons): array
    {
        return [
            'inline_keyboard' => $buttons
        ];
    }

    /**
     * Создает кнопку для inline клавиатуры
     */
    public static function inlineButton(string $text, string $callbackData): array
    {
        return [
            'text' => $text,
            'callback_data' => $callbackData,
        ];
    }

    /**
     * Создает главное меню с основными кнопками
     */
    public static function mainMenu(): array
    {
        return self::inlineKeyboard([
            [
                self::inlineButton('🚀 Запустить анализ', 'run'),
                self::inlineButton('📊 Статус', 'status'),
            ],
            [
                self::inlineButton('⚙️ Настройки', 'settings'),
                self::inlineButton('❓ Помощь', 'help'),
            ],
        ]);
    }

    /**
     * Создает кнопку "Назад в меню"
     */
    public static function backToMenu(): array
    {
        return self::inlineKeyboard([
            [self::inlineButton('🏠 Главное меню', 'start')]
        ]);
    }

}

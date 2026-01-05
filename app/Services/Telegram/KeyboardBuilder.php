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
                self::inlineButton('👷 Воркеры', 'worker'),
            ],
            [
                self::inlineButton('📜 История', 'history'),
                self::inlineButton('📊 Аналитика', 'analytics'),
            ],
            [
                self::inlineButton('⚙️ Настройки', 'settings'),
                self::inlineButton('🔧 Фильтры', 'filters'),
            ],
            [
                self::inlineButton('🔔 Уведомления', 'notifications'),
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

    /**
     * Создает меню управления воркерами
     */
    public static function workerManagementMenu(): array
    {
        return self::inlineKeyboard([
            [
                self::inlineButton('🔄 Обновить', 'worker'),
                self::inlineButton('🔄 Перезапустить', 'worker_restart'),
            ],
            [
                self::inlineButton('🧹 Очистить', 'worker_cleanup'),
            ],
            [
                self::inlineButton('🔙 Назад в меню', 'menu'),
            ],
        ]);
    }
}

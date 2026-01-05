<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    private TelegramBotService $botService;

    public function __construct(TelegramBotService $botService)
    {
        $this->botService = $botService;
    }

    /**
     * Обработка входящих webhook от Telegram
     */
    public function handle(Request $request)
    {
        try {
            $update = $request->all();

            Log::info('Получен webhook от Telegram', [
                'update_id' => $update['update_id'] ?? null,
                'message' => isset($update['message']) ? 'yes' : 'no',
                'callback_query' => isset($update['callback_query']) ? 'yes' : 'no',
            ]);

            // Обрабатываем обновление через бот-сервис
            $this->botService->handleUpdate($update);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка обработки Telegram webhook', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

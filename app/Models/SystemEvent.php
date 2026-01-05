<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для хранения событий системы
 * Используется для аналитики и мониторинга
 */
class SystemEvent extends Model
{
    protected $fillable = [
        'event_type',
        'event_data',
        'created_at'
    ];

    protected $casts = [
        'event_data' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false; // Используем только created_at

    /**
     * Получает события по типу за период
     */
    public static function getEventsByType(string $eventType, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('event_type', $eventType)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Получает статистику событий за период
     */
    public static function getEventStats(int $hours = 24): array
    {
        $events = static::where('created_at', '>=', now()->subHours($hours))
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();

        return [
            'opportunity_found' => $events['opportunity_found'] ?? 0,
            'system_error' => $events['system_error'] ?? 0,
            'parsing_complete' => $events['parsing_complete'] ?? 0,
            'analysis_complete' => $events['analysis_complete'] ?? 0,
            'system_status' => $events['system_status'] ?? 0,
            'total' => array_sum($events)
        ];
    }

    /**
     * Получает последние ошибки системы
     */
    public static function getRecentErrors(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('event_type', 'system_error')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Очищает старые события
     */
    public static function cleanupOldEvents(int $days = 30): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}

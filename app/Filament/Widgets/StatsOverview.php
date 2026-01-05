<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Всего пользователей', User::count())
                ->description('Зарегистрировано в системе')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Новых за сегодня', User::whereDate('created_at', today())->count())
                ->description('Зарегистрировано сегодня')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Активных пользователей', User::whereNotNull('email_verified_at')->count())
                ->description('С подтвержденным email')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('warning'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class SubscriptionPieChart extends ChartWidget
{
    protected static ?string $heading = 'Subscription Distribution (Pie)';

    protected function getData(): array
    {
        $data = Payment::selectRaw('subscription_id, COUNT(*) as total')
            ->groupBy('subscription_id')
            ->with('subscription')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->subscription->name => $item->total];
            });

        return [
            'datasets' => [
                [
                    'label' => 'Subscriptions',
                    'data' => $data->values(),
                    'backgroundColor' => [
                        '#6366F1', '#10B981', '#F59E0B', '#EF4444', '#3B82F6',
                    ],
                ],
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

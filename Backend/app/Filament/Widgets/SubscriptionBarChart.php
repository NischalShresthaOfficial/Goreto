<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SubscriptionBarChart extends ChartWidget
{
    protected static ?string $heading = 'Subscription Distribution (Bar)';

    protected function getData(): array
    {
        $data = Payment::selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
            ->whereYear('paid_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create()->month($month)->format('F');
        });

        $values = $months->map(function ($monthName, $index) use ($data) {
            $record = $data->firstWhere('month', $index + 1);
            return $record ? $record->total : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Amount (NPR)',
                    'data' => $values,
                    'backgroundColor' => '#3B82F6',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

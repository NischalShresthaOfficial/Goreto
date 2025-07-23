<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use App\Models\User;
use Filament\Widgets\ChartWidget;

abstract class GroupsByUserChart extends ChartWidget
{
    protected function getData(): array
    {
        $groupsCount = Group::selectRaw('created_by, COUNT(*) as total')
            ->groupBy('created_by')
            ->get();

        $labels = [];
        $data = [];

        foreach ($groupsCount as $group) {
            $user = User::find($group->created_by);
            $labels[] = $user ? $user->name : 'Unknown';
            $data[] = $group->total;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Groups Created',
                    'data' => $data,
                    'backgroundColor' => $this->getBackgroundColors(count($data)),
                ],
            ],
        ];
    }

    protected function getBackgroundColors(int $count): array
    {
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#8AFFC1', '#FF8A8A', '#8A8AFF', '#FFC28A',
        ];

        return array_slice($colors, 0, $count);
    }
}

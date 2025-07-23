<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class PostsByUserPieChart extends ChartWidget
{
    protected static ?string $heading = 'Posts Created by Users (Pie)';

    protected function getData(): array
    {
        $postsCount = Post::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->get();

        $labels = [];
        $data = [];

        foreach ($postsCount as $post) {
            $user = User::find($post->user_id);
            $labels[] = $user ? $user->name : 'Unknown';
            $data[] = $post->total;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#8AFFC1', '#FF8A8A', '#8A8AFF', '#FFC28A',
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

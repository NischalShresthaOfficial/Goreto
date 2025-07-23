<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class PostsByUserBarChart extends ChartWidget
{
    protected static ?string $heading = 'Posts Created by Users (Bar)';

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
                    'label' => 'Posts Created',
                    'data' => $data,
                    'backgroundColor' => '#3B82F6',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

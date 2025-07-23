<?php

namespace App\Filament\Widgets;

class GroupsByUserBarChart extends GroupsByUserChart
{
    protected static ?string $heading = 'Groups Created by Users (Bar)';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getBackgroundColors(int $count): array
    {
        return array_fill(0, $count, '#3B82F6');
    }
}

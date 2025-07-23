<?php

namespace App\Filament\Widgets;

class GroupsByUserPieChart extends GroupsByUserChart
{
    protected static ?string $heading = 'Groups Created by Users (Pie)';

    protected function getType(): string
    {
        return 'pie';
    }
}

<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run()
    {
        Subscription::query()->delete();

        Subscription::insert([
            [
                'name' => '1 Month Plan',
                'price' => 875,
                'currency' => 'NPR',
                'duration' => 'monthly',
                'duration_days' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '3 Months Plan',
                'price' => 2400,
                'currency' => 'NPR',
                'duration' => 'quarterly',
                'duration_days' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '1 Year Plan',
                'price' => 8400,
                'currency' => 'NPR',
                'duration' => 'yearly',
                'duration_days' => 365,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

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
                'currency' => 'USD',
                'duration' => 'monthly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '3 Months Plan',
                'price' => 2400,
                'currency' => 'USD',
                'duration' => 'quarterly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '1 Year Plan',
                'price' => 8400,
                'currency' => 'USD',
                'duration' => 'yearly',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

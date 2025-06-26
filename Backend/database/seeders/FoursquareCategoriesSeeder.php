<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoursquareCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['fsq_category_id' => '63be6904847c3692a84b9b9a', 'category' => 'Community and Government'],
            ['fsq_category_id' => '63be6904847c3692a84b9bb5', 'category' => 'Dining and Drinking'],
            ['fsq_category_id' => '4d4b7104d754a06370d81259', 'category' => 'Arts & Entertainment'],
            ['fsq_category_id' => '4d4b7105d754a06375d81259', 'category' => 'Business and Professional Services'],
            ['fsq_category_id' => '4d4b7105d754a06373d81259', 'category' => 'Event'],
            ['fsq_category_id' => '63be6904847c3692a84b9bb9', 'category' => 'Health and Medicine'],
            ['fsq_category_id' => '4d4b7105d754a06374d81259', 'category' => 'Restaurant'],
            ['fsq_category_id' => '4d4b7105d754a06376d81259', 'category' => 'Nightlife Spot'],
            ['fsq_category_id' => '4d4b7105d754a06372d81259', 'category' => 'College & University'],
            ['fsq_category_id' => '4d4b7105d754a06378d81259', 'category' => 'Retail'],
            ['fsq_category_id' => '4d4b7105d754a06379d81259', 'category' => 'Travel & Transport'],
            ['fsq_category_id' => '4e67e38e036454776db1fb3a', 'category' => 'Residential Building'],
            ['fsq_category_id' => '4d4b7105d754a06377d81259', 'category' => 'Landmarks and Outdoors'],
            ['fsq_category_id' => '4f4528bc4b90abdf24c9de85', 'category' => 'Sports and Recreation'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['fsq_category_id' => $cat['fsq_category_id']],
                ['category' => $cat['category'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Foursquare categories seeded with actual Foursquare IDs.');
    }
}

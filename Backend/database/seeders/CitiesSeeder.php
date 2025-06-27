<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CitiesSeeder extends Seeder
{
    public function run()
    {
        $username = env('GEONAMES_USERNAME');

        $response = Http::get('http://api.geonames.org/searchJSON', [
            'country' => 'NP',
            'featureClass' => 'P',
            'maxRows' => 1000,
            'username' => $username,
        ]);

        if ($response->successful()) {
            $cities = $response->json('geonames');

            foreach ($cities as $cityData) {
                City::updateOrCreate(
                    ['city' => $cityData['name']],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            $this->command->info('Nepal cities imported successfully.');
        } else {
            $this->command->error('Failed to fetch cities from GeoNames API.');
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $response = Http::get('https://restcountries.com/v3.1/all?fields=name');

        if ($response->failed()) {
            $this->command->error('Failed to fetch countries from API.');

            return;
        }

        $countries = $response->json();

        foreach ($countries as $countryData) {
            $name = $countryData['name']['common'] ?? null;

            if ($name) {
                Country::firstOrCreate(['country' => $name]);
            }
        }

        $this->command->info('Countries seeded successfully.');
    }
}

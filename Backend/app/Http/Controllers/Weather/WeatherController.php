<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityWeatherCondition;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function fetchAndStoreWeather($cityId)
    {
        $city = City::find($cityId);

        if (! $city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        $apiKey = env('OPENWEATHERMAP_KEY');

        $query = $city->city.',NP';

        $url = 'https://api.openweathermap.org/data/2.5/weather?q='.urlencode($query)."&appid={$apiKey}&units=metric";

        $response = Http::get($url);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Failed to fetch weather data',
                'error' => $response->json(),
            ], $response->status());
        }

        $data = $response->json();

        $weather = CityWeatherCondition::updateOrCreate(
            ['city_id' => $city->id],
            [
                'description' => $data['weather'][0]['description'] ?? 'No description',
                'temperature' => $data['main']['temp'] ?? 0,
                'humidity' => $data['main']['humidity'] ?? 0,
            ]
        );

        return response()->json([
            'message' => 'Weather data fetched and saved successfully',
            'weather' => $weather,
        ]);
    }
}

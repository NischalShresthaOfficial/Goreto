<?php

namespace App\Http\Controllers\TextToSpeech;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CloudSpeechController extends Controller
{
    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'filename' => 'nullable|string',
        ]);

        $apiKey = env('GOOGLE_API_KEY');

        $response = Http::post(
            'https://texttospeech.googleapis.com/v1/text:synthesize?key='.$apiKey,
            [
                'input' => ['text' => $request->text],
                'voice' => ['languageCode' => 'en-US', 'ssmlGender' => 'FEMALE'],
                'audioConfig' => ['audioEncoding' => 'MP3'],
            ]
        );

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        $audioContent = base64_decode($response['audioContent']);
        $filename = $request->filename ?? 'tts_'.time().'.mp3';
        $path = 'tts/'.date('Y-m-d').'/'.$filename;

        Storage::disk('public')->put($path, $audioContent);

        return response()->json([
            'message' => 'Audio file generated successfully',
            'file_url' => Storage::disk('public')->url($path),
        ]);
    }

    public function speechToText(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:wav,mp3,flac',
        ]);

        $apiKey = env('GOOGLE_API_KEY');
        $audioFile = $request->file('audio_file');
        $extension = $audioFile->extension();

        $encoding = match ($extension) {
            'mp3' => 'MP3',
            'flac' => 'FLAC',
            'wav' => 'LINEAR16',
            default => 'LINEAR16',
        };

        $audioContent = base64_encode(file_get_contents($audioFile->getRealPath()));

        $response = Http::post(
            'https://speech.googleapis.com/v1/speech:recognize?key='.$apiKey,
            [
                'config' => [
                    'encoding' => $encoding,
                    'languageCode' => 'en-US',
                ],
                'audio' => ['content' => $audioContent],
            ]
        );

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        $transcription = '';
        foreach ($response['results'] ?? [] as $result) {
            $transcription .= $result['alternatives'][0]['transcript'] ?? '';
        }

        return response()->json([
            'transcription' => $transcription,
        ]);
    }
}

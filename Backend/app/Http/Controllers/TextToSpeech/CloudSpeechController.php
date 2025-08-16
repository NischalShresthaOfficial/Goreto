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
            'source_lang' => 'nullable|string',
            'target_lang' => 'nullable|string',
            'filename' => 'nullable|string',
        ]);

        $text = $request->text;
        $sourceLang = $request->source_lang ?? 'en';
        $targetLang = $request->target_lang ?? $sourceLang;

        if ($sourceLang !== $targetLang) {
            $translateApiKey = env('GOOGLE_TRANSLATE_API_KEY');
            $translateResponse = Http::post(
                "https://translation.googleapis.com/language/translate/v2?key={$translateApiKey}",
                [
                    'q' => $text,
                    'source' => $sourceLang,
                    'target' => $targetLang,
                    'format' => 'text',
                ]
            );

            if ($translateResponse->successful()) {
                $text = $translateResponse['data']['translations'][0]['translatedText'] ?? $text;
            }
        }

        $supportedTTSLanguages = ['en' => 'en-US'];
        $languageCode = $supportedTTSLanguages[$targetLang] ?? 'en-US';

        $ttsApiKey = env('GOOGLE_TTS_API_KEY');

        $response = Http::post(
            'https://texttospeech.googleapis.com/v1/text:synthesize?key='.$ttsApiKey,
            [
                'input' => ['text' => $text],
                'voice' => ['languageCode' => $languageCode, 'ssmlGender' => 'FEMALE'],
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
            'translated_text' => $text,
            'tts_language' => $languageCode,
        ]);
    }

    public function speechToText(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:wav,mp3,flac,m4a',
            'language' => 'nullable|string',
        ]);

        $language = $request->language ?? 'en';
        $sttApiKey = env('GOOGLE_STT_API_KEY');

        $audioFile = $request->file('audio_file');
        $extension = $audioFile->extension();

        $encoding = match ($extension) {
            'mp3' => 'MP3',
            'flac' => 'FLAC',
            'wav' => 'LINEAR16',
            'm4a' => 'LINEAR16',
            default => 'LINEAR16',
        };

        $audioContent = base64_encode(file_get_contents($audioFile->getRealPath()));

        $languageCode = match ($language) {
            'ne' => 'ne-NP',
            'en' => 'en-US',
            default => 'en-US',
        };

        $response = Http::post(
            'https://speech.googleapis.com/v1/speech:recognize?key='.$sttApiKey,
            [
                'config' => [
                    'encoding' => $encoding,
                    'languageCode' => $languageCode,
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
            'language' => $languageCode,
        ]);
    }
}

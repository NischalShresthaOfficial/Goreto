<?php

namespace App\Http\Controllers\TextToSpeech;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            'voice_name' => 'nullable|string',
            'speaking_rate' => 'nullable|numeric|min:0.25|max:4.0',
            'pitch' => 'nullable|numeric|min:-20.0|max:20.0',
        ]);

        $text = $request->text;
        $sourceLang = $request->source_lang ?? 'en';
        $targetLang = $request->target_lang ?? $sourceLang;
        $voiceName = $request->voice_name;
        $speakingRate = $request->speaking_rate ?? 1.0;
        $pitch = $request->pitch ?? 0.0;

        Log::info('TTS Request', [
            'text' => $text,
            'source' => $sourceLang,
            'target' => $targetLang,
            'voice' => $voiceName,
        ]);

        if ($sourceLang !== $targetLang) {
            $translatedText = $this->translateText($text, $sourceLang, $targetLang);
            if ($translatedText) {
                $text = $translatedText;
                Log::info('Text translated', ['original' => $request->text, 'translated' => $text]);
            }
        }

        return $this->generateGoogleTTS($text, $targetLang, $request->filename, $voiceName, $speakingRate, $pitch);
    }

    private function generateGoogleTTS($text, $language, $filename = null, $voiceName = null, $speakingRate = 1.0, $pitch = 0.0)
    {
        $ttsApiKey = env('GOOGLE_TTS_API_KEY');

        if (! $ttsApiKey) {
            return response()->json(['error' => 'Google TTS API key not configured'], 500);
        }

        $languageConfig = $this->getLanguageConfig($language, $voiceName);

        try {
            $payload = [
                'input' => ['text' => $text],
                'voice' => [
                    'languageCode' => $languageConfig['code'],
                    'name' => $languageConfig['voice'],
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3',
                    'speakingRate' => $speakingRate,
                    'pitch' => $pitch,
                    'volumeGainDb' => 0.0,
                    'sampleRateHertz' => 24000,
                ],
            ];

            Log::info('Google TTS Request', $payload);

            $response = Http::timeout(30)->post(
                'https://texttospeech.googleapis.com/v1/text:synthesize?key='.$ttsApiKey,
                $payload
            );

            if ($response->failed()) {
                Log::error('Google TTS API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Google TTS failed',
                    'details' => $response->json(),
                    'status_code' => $response->status(),
                ], 500);
            }

            $responseData = $response->json();

            if (! isset($responseData['audioContent'])) {
                Log::error('No audio content in response', $responseData);

                return response()->json(['error' => 'No audio content received from Google TTS'], 500);
            }

            $audioContent = base64_decode($responseData['audioContent']);
            $filename = $filename ?? $this->generateFilename($language);
            $path = 'tts/'.date('Y-m-d').'/'.$filename;

            $fullDir = storage_path('app/public/tts/'.date('Y-m-d'));
            if (! is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }

            Storage::disk('public')->put($path, $audioContent);

            Log::info('TTS Generated Successfully', [
                'path' => $path,
                'language' => $languageConfig['code'],
                'voice' => $languageConfig['voice'],
            ]);

            return response()->json([
                'message' => 'Audio file generated successfully',
                'file_url' => Storage::disk('public')->url($path),
                'file_path' => $path,
                'translated_text' => $text,
                'original_text' => request()->text,
                'tts_language' => $languageConfig['code'],
                'voice_name' => $languageConfig['voice'],
                'audio_config' => [
                    'speaking_rate' => $speakingRate,
                    'pitch' => $pitch,
                    'sample_rate' => 24000,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Google TTS Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'TTS generation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getLanguageConfig($language, $customVoice = null)
    {
        $configs = [
            'en' => [
                'code' => 'en-US',
                'voices' => [
                    'en-US-Journey-D', 'en-US-Journey-F', 'en-US-Journey-O',
                    'en-US-Neural2-A', 'en-US-Neural2-C', 'en-US-Neural2-D',
                    'en-US-Neural2-E', 'en-US-Neural2-F', 'en-US-Neural2-G',
                    'en-US-Neural2-H', 'en-US-Neural2-I', 'en-US-Neural2-J',
                    'en-US-Standard-A', 'en-US-Standard-B', 'en-US-Standard-C',
                    'en-US-Standard-D', 'en-US-Standard-E', 'en-US-Standard-F',
                    'en-US-Standard-G', 'en-US-Standard-H', 'en-US-Standard-I',
                    'en-US-Standard-J',
                ],
            ],
            'hi' => [
                'code' => 'hi-IN',
                'voices' => [
                    'hi-IN-Neural2-A', 'hi-IN-Neural2-B', 'hi-IN-Neural2-C', 'hi-IN-Neural2-D',
                    'hi-IN-Standard-A', 'hi-IN-Standard-B', 'hi-IN-Standard-C', 'hi-IN-Standard-D',
                ],
            ],
            'bn' => [
                'code' => 'bn-IN',
                'voices' => [
                    'bn-IN-Standard-A', 'bn-IN-Standard-B',
                ],
            ],
            'ne' => [
                'code' => 'hi-IN',
                'voices' => [
                    'hi-IN-Neural2-A', 'hi-IN-Neural2-B', 'hi-IN-Neural2-C', 'hi-IN-Neural2-D',
                ],
            ],
        ];

        $config = $configs[$language] ?? $configs['en'];

        if ($customVoice && in_array($customVoice, $config['voices'])) {
            $config['voice'] = $customVoice;
        } else {
            $config['voice'] = $config['voices'][0];
        }

        return $config;
    }

    private function generateFilename($language)
    {
        $prefix = match ($language) {
            'ne' => 'nepali',
            'hi' => 'hindi',
            'bn' => 'bengali',
            'en' => 'english',
            default => 'tts'
        };

        return $prefix.'_'.time().'_'.uniqid().'.mp3';
    }

    private function translateText($text, $sourceLang, $targetLang)
    {
        $translateApiKey = env('GOOGLE_TRANSLATE_API_KEY');

        if (! $translateApiKey) {
            Log::warning('Google Translate API key not available');

            return null;
        }

        try {
            $response = Http::timeout(30)->post(
                "https://translation.googleapis.com/language/translate/v2?key={$translateApiKey}",
                [
                    'q' => $text,
                    'source' => $sourceLang,
                    'target' => $targetLang,
                    'format' => 'text',
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                $translatedText = $data['data']['translations'][0]['translatedText'] ?? null;

                Log::info('Translation successful', [
                    'source' => $sourceLang,
                    'target' => $targetLang,
                    'original' => $text,
                    'translated' => $translatedText,
                ]);

                return $translatedText;
            }

            Log::error('Translation failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Translation exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function speechToText(Request $request)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:wav,mp3,flac,m4a,ogg,webm',
            'language' => 'nullable|string',
            'model' => 'nullable|string',
            'use_enhanced' => 'nullable|boolean',
            'enable_word_time_offsets' => 'nullable|boolean',
            'enable_automatic_punctuation' => 'nullable|boolean',
        ]);

        $language = $request->language ?? 'en';
        $model = $request->model ?? 'latest_long';
        $useEnhanced = $request->use_enhanced ?? true;
        $enableWordTimeOffsets = $request->enable_word_time_offsets ?? false;
        $enableAutomaticPunctuation = $request->enable_automatic_punctuation ?? true;

        Log::info('STT Request', [
            'language' => $language,
            'model' => $model,
            'enhanced' => $useEnhanced,
        ]);

        return $this->processGoogleSTT(
            $request->file('audio_file'),
            $language,
            $model,
            $useEnhanced,
            $enableWordTimeOffsets,
            $enableAutomaticPunctuation
        );
    }

    private function processGoogleSTT($audioFile, $language, $model = 'latest_long', $useEnhanced = true, $enableWordTimeOffsets = false, $enableAutomaticPunctuation = true)
    {
        $sttApiKey = env('GOOGLE_STT_API_KEY');

        if (! $sttApiKey) {
            return response()->json(['error' => 'Google STT API key not configured'], 500);
        }

        try {
            $audioData = $this->processAudioFile($audioFile);
            if (! $audioData) {
                return response()->json(['error' => 'Failed to process audio file'], 500);
            }

            $languageConfig = $this->getSTTLanguageConfig($language);

            $payload = [
                'config' => [
                    'encoding' => $audioData['encoding'],
                    'sampleRateHertz' => $audioData['sampleRate'],
                    'languageCode' => $languageConfig['primary'],
                    'alternativeLanguageCodes' => $languageConfig['alternatives'],
                    'model' => $model,
                    'useEnhanced' => $useEnhanced,
                    'enableWordTimeOffsets' => $enableWordTimeOffsets,
                    'enableAutomaticPunctuation' => $enableAutomaticPunctuation,
                    'audioChannelCount' => 1,
                    'enableSeparateRecognitionPerChannel' => false,
                ],
                'audio' => [
                    'content' => base64_encode($audioData['content']),
                ],
            ];

            Log::info('Google STT Request Config', $payload['config']);

            $response = Http::timeout(60)->post(
                'https://speech.googleapis.com/v1/speech:recognize?key='.$sttApiKey,
                $payload
            );

            if ($response->failed()) {
                Log::error('Google STT API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Google STT failed',
                    'details' => $response->json(),
                    'status_code' => $response->status(),
                ], 500);
            }

            $responseData = $response->json();

            $results = $this->processSTTResults($responseData);

            Log::info('STT Processing completed', [
                'transcription_length' => strlen($results['transcription']),
                'confidence' => $results['confidence'],
            ]);

            return response()->json([
                'transcription' => $results['transcription'],
                'language' => $languageConfig['primary'],
                'confidence' => $results['confidence'],
                'alternatives' => $results['alternatives'],
                'word_time_offsets' => $results['word_time_offsets'],
                'audio_duration' => $audioData['duration'] ?? null,
                'model_used' => $model,
            ]);

        } catch (\Exception $e) {
            Log::error('Google STT Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'STT processing failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function processAudioFile($audioFile)
    {
        try {
            $extension = strtolower($audioFile->extension());
            $originalPath = $audioFile->getRealPath();

            if (in_array($extension, ['m4a', 'ogg', 'webm'])) {
                $convertedPath = storage_path('app/temp/audio_'.time().'.wav');

                $tempDir = dirname($convertedPath);
                if (! is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $command = sprintf(
                    'ffmpeg -i %s -ac 1 -ar 16000 -f wav %s 2>&1',
                    escapeshellarg($originalPath),
                    escapeshellarg($convertedPath)
                );

                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || ! file_exists($convertedPath)) {
                    Log::error('Audio conversion failed', [
                        'command' => $command,
                        'output' => $output,
                        'return_var' => $returnVar,
                    ]);

                    return null;
                }

                $audioPath = $convertedPath;
                $encoding = 'LINEAR16';
                $sampleRate = 16000;
            } else {
                $audioPath = $originalPath;
                $encoding = match ($extension) {
                    'mp3' => 'MP3',
                    'flac' => 'FLAC',
                    'wav' => 'LINEAR16',
                    default => 'LINEAR16',
                };
                $sampleRate = 16000;
            }

            $content = file_get_contents($audioPath);

            if (isset($convertedPath) && file_exists($convertedPath)) {
                unlink($convertedPath);
            }

            return [
                'content' => $content,
                'encoding' => $encoding,
                'sampleRate' => $sampleRate,
                'duration' => null,
            ];

        } catch (\Exception $e) {
            Log::error('Audio processing failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function getSTTLanguageConfig($language)
    {
        $configs = [
            'en' => [
                'primary' => 'en-US',
                'alternatives' => ['en-GB', 'en-AU'],
            ],
            'hi' => [
                'primary' => 'hi-IN',
                'alternatives' => ['en-IN'],
            ],
            'bn' => [
                'primary' => 'bn-IN',
                'alternatives' => ['hi-IN', 'en-IN'],
            ],
            'ne' => [
                'primary' => 'hi-IN',
                'alternatives' => ['en-IN', 'bn-IN'],
            ],
        ];

        return $configs[$language] ?? $configs['en'];
    }

    private function processSTTResults($responseData)
    {
        $transcription = '';
        $confidence = 0;
        $alternatives = [];
        $wordTimeOffsets = [];

        foreach ($responseData['results'] ?? [] as $result) {
            $alternative = $result['alternatives'][0] ?? [];

            if (isset($alternative['transcript'])) {
                $transcription .= $alternative['transcript'];
                $confidence = max($confidence, $alternative['confidence'] ?? 0);

                if (isset($alternative['words'])) {
                    $wordTimeOffsets = array_merge($wordTimeOffsets, $alternative['words']);
                }
            }

            foreach ($result['alternatives'] ?? [] as $alt) {
                if (isset($alt['transcript']) && $alt['transcript'] !== $alternative['transcript']) {
                    $alternatives[] = [
                        'transcript' => $alt['transcript'],
                        'confidence' => $alt['confidence'] ?? 0,
                    ];
                }
            }
        }

        return [
            'transcription' => trim($transcription),
            'confidence' => $confidence,
            'alternatives' => $alternatives,
            'word_time_offsets' => $wordTimeOffsets,
        ];
    }
}

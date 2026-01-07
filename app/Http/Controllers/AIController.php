<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AIController extends Controller
{
    public function generateImage(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'style' => 'required|in:classic-painting,disney-animation',
        ]);

        $photo = $request->file('photo');
        $style = $request->input('style');

        // Upload photo to storage
        $photoPath = $photo->store('uploads', 'public');
        $photoUrl = Storage::url($photoPath);

        // Generate prompt based on style
        $prompt = match($style) {
            'classic-painting' => 'Transform this wedding photo into a classic romantic painting with soft brush strokes, warm colors, and a romantic atmosphere',
            'disney-animation' => 'Transform this wedding photo into a Disney-style animated illustration with joyful characters, pastel background, and animated style',
        };

        // Call Gemini API for image generation
        $apiKey = config('services.gemini.api_key');
        
        // Note: Gemini API might need different approach for image generation
        // This is a placeholder - adjust based on actual Gemini API capabilities
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt,
                            ],
                            [
                                'inline_data' => [
                                    'mime_type' => $photo->getMimeType(),
                                    'data' => base64_encode(file_get_contents($photo->getRealPath())),
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            // For now, return the original photo URL
            // In production, you'd process the Gemini response and generate/store the styled image
            return response()->json([
                'image_url' => $photoUrl,
                'original_url' => $photoUrl,
            ]);
        } catch (\Exception $e) {
            // Fallback to original photo if API fails
            return response()->json([
                'image_url' => $photoUrl,
                'original_url' => $photoUrl,
            ]);
        }
    }

    public function generateText(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $apiKey = config('services.gemini.api_key');

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $request->input('prompt'),
                            ],
                        ],
                    ],
                ],
            ]);

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return response()->json(['text' => $text]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate text'], 500);
        }
    }
}


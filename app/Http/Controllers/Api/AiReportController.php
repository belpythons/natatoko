<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiReportController extends Controller
{
    /**
     * Generate an AI-powered report using Gemini API.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query_text' => 'required|string|max:2000',
        ]);

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Gemini API key belum dikonfigurasi.',
            ], 500);
        }

        $systemPrompt = "Kamu adalah seorang analis data senior untuk sistem Point of Sales (POS) sebuah coffee shop & bakery bernama \"Posita Coffee\".\n"
            . "Tugasmu adalah menjawab pertanyaan bisnis berdasarkan data yang diberikan.\n"
            . "Gunakan bahasa Indonesia yang profesional.\n"
            . "Berikan insight yang actionable dan ringkas.\n"
            . "Format jawaban menggunakan Markdown jika diperlukan.\n"
            . "Jangan memberikan informasi di luar konteks POS dan bisnis F&B.";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey,
            [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $validated['query_text']],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ],
            ]
            );

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Gagal menghubungi Gemini API.',
                    'details' => $response->json(),
                ], $response->status());
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Tidak ada respons dari AI.';

            return response()->json([
                'response' => $text,
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses permintaan AI.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
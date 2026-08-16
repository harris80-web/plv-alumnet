<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Answers alumni/employer chatbot questions grounded in the FAQ knowledge
 * base (see App\Models\Faq) — same Gemini calling convention as
 * GeminiResumeParser (structured JSON output via responseSchema), but the
 * prompt instructs the model to answer ONLY from the supplied FAQ content
 * and admit when it doesn't know, rather than free-associating. The caller
 * (ChatbotController) escalates to a live agent whenever answered=false,
 * the API isn't configured, or the request fails — a broken/unavailable AI
 * should hand off to a human, not fail the conversation.
 */
class GeminiChatbotService
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param  array<int, array{faq_question: string, faq_answer: string}>  $faqs
     * @param  array<int, array{role: string, text: string}>  $history  Prior turns, oldest first.
     * @return array{answered: bool, answer: ?string}
     *
     * @throws RuntimeException on any failure — caller escalates to a live agent.
     */
    public function ask(string $question, array $faqs, array $history = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->timeout(20)
            ->post(sprintf(self::ENDPOINT, $this->model), [
                'contents' => [[
                    'parts' => [['text' => $this->buildPrompt($question, $faqs, $history)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'answered' => ['type' => 'BOOLEAN'],
                            'answer' => ['type' => 'STRING', 'nullable' => true],
                        ],
                        'required' => ['answered'],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini API request failed: ' . $response->status() . ' ' . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned an empty or unexpected response.');
        }

        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Gemini did not return valid JSON: ' . json_last_error_msg());
        }

        $answered = (bool) ($decoded['answered'] ?? false);
        $answer = $answered ? trim((string) ($decoded['answer'] ?? '')) : null;

        // $answer is null (not '') on the "couldn't answer" path — checking
        // against '' let a null slip through to mb_substr() below and trip
        // a deprecation warning on every declined question.
        return [
            'answered' => $answered && !empty($answer),
            'answer' => !empty($answer) ? mb_substr($answer, 0, 1500) : null,
        ];
    }

    private function buildPrompt(string $question, array $faqs, array $history): string
    {
        $kb = collect($faqs)
            ->map(fn ($f, $i) => ($i + 1) . ". Q: {$f['faq_question']}\n   A: {$f['faq_answer']}")
            ->implode("\n");
        $kb = $kb !== '' ? $kb : '(no FAQ entries available)';

        $historyText = collect($history)
            ->map(fn ($turn) => strtoupper($turn['role']) . ': ' . $turn['text'])
            ->implode("\n");

        return <<<PROMPT
            You are the support chatbot for PLV-AlumNet, a university alumni platform.
            Answer the user's latest question using ONLY the knowledge base below —
            do not invent policies, dates, or features that aren't listed.

            Knowledge base:
            {$kb}

            Rules:
            - If a knowledge base entry answers the question (even loosely — rephrase it
              in your own words, friendly and concise, 1-3 sentences), set answered=true
              and put the answer in "answer".
            - If nothing in the knowledge base actually covers the question, or you are
              not confident, set answered=false and leave "answer" null. Do NOT guess.
            - Never ask the user to wait — the caller escalates to a human agent when
              answered=false.

            Recent conversation (oldest first):
            {$historyText}

            User's latest question: "{$question}"
            PROMPT;
    }
}

<?php

namespace App\Infrastructure\AI;

use App\Domain\AI\PriorityAnalyzer;
use Illuminate\Support\Facades\Http;

class OpenAIPriorityAnalyzer implements PriorityAnalyzer
{
    public function analyze(string $newMessage, array $generatedReports): ?array
    {
        $promise = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'temperature' => 0,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'new_report' => ['message' => $newMessage],
                            'existing_generated_reports' => $generatedReports,
                        ]),
                    ],
                ],
            ]);

        $rawResponse = $promise->wait();

        if ($rawResponse->getStatusCode() >= 400) {
            throw new \Exception('Request failed: ' . $rawResponse->getStatusCode());
        }

        $bodyString = (string) $rawResponse->getBody();
        $aiJson = json_decode($bodyString, true)['choices'][0]['message']['content'] ?? null;

        return $aiJson;
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
            You are an AI system that classifies student reports.

            Your job:
            1. Determine whether a new report is semantically similar to an existing generated report.
            2. If similar, return the ID of the existing generated report.
            3. If not similar, generate a new normalized report.

            Rules:
            - ONLY respond in valid JSON
            - Use "repeated" or "new" as type
            - Priority must be P0, P1, or P2
        PROMPT;
    }
}

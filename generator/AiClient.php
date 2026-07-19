<?php
declare(strict_types=1);

class AiClient
{
    private string $apiUrl;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiUrl = rtrim($_ENV['AI_API_URL'] ?? 'https://api.openai.com/v1', '/');
        $this->apiKey = $_ENV['AI_API_KEY'] ?? '';
        $this->model  = $_ENV['AI_MODEL'] ?? 'gpt-4o-mini';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        $url = $this->apiUrl . '/chat/completions';

        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens'  => 4096,
            'response_format' => ['type' => 'json_object'],
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException('AI API request failed: ' . $error);
        }

        $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? 'HTTP ' . $httpCode;
            throw new RuntimeException('AI API error: ' . $msg);
        }

        return $data['choices'][0]['message']['content'] ?? '';
    }
}

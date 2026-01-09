<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * Claude API Library
 *
 * Handles communication with Anthropic's Claude API
 */
class ClaudeAPI {
    private $apiKey;
    private $baseUrl = 'https://api.anthropic.com/v1';
    private $model = 'claude-sonnet-4-5-20250929';

    /**
     * Constructor
     *
     * @param string|null $apiKey Claude API Key (defaults to env variable)
     */
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?: getenv('CLAUDE_API_KEY');

        if (!$this->apiKey) {
            error_log('Claude API: No API key provided');
        }
    }

    /**
     * Send a message to Claude
     *
     * @param string $prompt The prompt to send
     * @param int $maxTokens Maximum tokens in response
     * @return array|false Response data or false on failure
     */
    public function message($prompt, $maxTokens = 4096) {
        if (!$this->apiKey) {
            error_log('Claude API: Cannot send message without API key. Check CLAUDE_API_KEY in .env file.');
            throw new Exception('Claude API key not configured. Please set CLAUDE_API_KEY in your .env file.');
        }

        $url = $this->baseUrl . '/messages';

        $data = [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minute timeout

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('Claude API cURL Error: ' . $curlError);
            return false;
        }

        if ($httpCode !== 200) {
            error_log('Claude API Error: HTTP ' . $httpCode . ' - ' . substr($response, 0, 500));
            return false;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Claude API: Invalid JSON response');
            return false;
        }

        return $responseData;
    }

    /**
     * Extract text content from Claude response
     *
     * @param array $response Response from message()
     * @return string|false Text content or false on failure
     */
    public function extractText($response) {
        if (!$response || !isset($response['content']) || !is_array($response['content'])) {
            return false;
        }

        foreach ($response['content'] as $block) {
            if ($block['type'] === 'text') {
                return $block['text'];
            }
        }

        return false;
    }

    /**
     * Send prompt and get text response
     *
     * @param string $prompt The prompt to send
     * @param int $maxTokens Maximum tokens in response
     * @return string|false Text response or false on failure
     */
    public function getText($prompt, $maxTokens = 4096) {
        $response = $this->message($prompt, $maxTokens);
        if (!$response) {
            return false;
        }
        return $this->extractText($response);
    }
}

<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * OpenAI API Library
 *
 * Handles communication with OpenAI's API for chat completions and embeddings
 */
class OpenAIAPI {
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';
    private $chatModel = 'gpt-4o-mini';
    private $embeddingModel = 'text-embedding-3-small';

    /**
     * Constructor
     *
     * @param string|null $apiKey OpenAI API Key (defaults to env variable)
     */
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?: getenv('OPENAI_API_KEY');

        if (!$this->apiKey) {
            error_log('OpenAI API: No API key provided');
        }
    }

    /**
     * Send a chat completion request
     *
     * @param array $messages Array of message objects [{role, content}]
     * @param string|null $model Model to use (defaults to gpt-4o-mini)
     * @param int $maxTokens Maximum tokens in response
     * @return array|false Response data or false on failure
     */
    public function chat($messages, $model = null, $maxTokens = 1000) {
        if (!$this->apiKey) {
            error_log('OpenAI API: Cannot send message without API key. Check OPENAI_API_KEY in .env file.');
            throw new Exception('OpenAI API key not configured. Please set OPENAI_API_KEY in your .env file.');
        }

        $url = $this->baseUrl . '/chat/completions';
        $model = $model ?: $this->chatModel;

        $data = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => 0.7
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        $jsonPayload = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('OpenAI API: JSON encoding error: ' . json_last_error_msg());
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('OpenAI API cURL Error: ' . $curlError);
            return false;
        }

        if ($httpCode !== 200) {
            error_log('OpenAI API Error: HTTP ' . $httpCode . ' - ' . substr($response, 0, 500));
            return false;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('OpenAI API: Invalid JSON response');
            return false;
        }

        return $responseData;
    }

    /**
     * Extract text content from chat response
     *
     * @param array $response Response from chat()
     * @return string|false Text content or false on failure
     */
    public function extractText($response) {
        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            return false;
        }

        return $response['choices'][0]['message']['content'];
    }

    /**
     * Send chat request and get text response
     *
     * @param array $messages Array of message objects
     * @param string|null $model Model to use
     * @param int $maxTokens Maximum tokens
     * @return string|false Text response or false on failure
     */
    public function getChatText($messages, $model = null, $maxTokens = 1000) {
        $response = $this->chat($messages, $model, $maxTokens);
        if (!$response) {
            return false;
        }
        return $this->extractText($response);
    }

    /**
     * Generate embedding for text
     *
     * @param string $text Text to embed
     * @param string|null $model Embedding model to use
     * @return array|false Vector array or false on failure
     */
    public function embedding($text, $model = null) {
        if (!$this->apiKey) {
            error_log('OpenAI API: Cannot generate embedding without API key');
            throw new Exception('OpenAI API key not configured');
        }

        $url = $this->baseUrl . '/embeddings';
        $model = $model ?: $this->embeddingModel;

        $data = [
            'model' => $model,
            'input' => $text
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('OpenAI Embedding cURL Error: ' . $curlError);
            return false;
        }

        if ($httpCode !== 200) {
            error_log('OpenAI Embedding Error: HTTP ' . $httpCode . ' - ' . substr($response, 0, 500));
            return false;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('OpenAI Embedding: Invalid JSON response');
            return false;
        }

        if (!isset($responseData['data'][0]['embedding'])) {
            return false;
        }

        return $responseData['data'][0]['embedding'];
    }
}

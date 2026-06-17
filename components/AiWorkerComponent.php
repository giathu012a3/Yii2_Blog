<?php

declare(strict_types=1);

namespace app\components;

use yii\base\Component;
use yii\web\HttpException;

class AiWorkerComponent extends Component
{
    public $accountId;
    public $workerToken;
    public $model;
    public $workerUrl;

    /**
     * {@inheritdoc}
     * @throws HttpException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->workerUrl) && empty($this->accountId)) {
            throw new HttpException(502, 'AI service is not configured (missing Cloudflare account ID or Worker URL).');
        }

        if (empty($this->workerToken)) {
            throw new HttpException(502, 'AI service is not configured (missing Cloudflare Worker token).');
        }
    }

    /**
     * Calls Cloudflare Workers AI model API.
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param array $options Additional options to merge into request body
     * @return string
     * @throws HttpException
     */
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): string
    {
        $url = !empty($this->workerUrl)
            ? $this->workerUrl
            : sprintf('https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s', $this->accountId, $this->model);

        $payload = array_merge([
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ], $options);

        $ch = curl_init($url);
        if ($ch === false) {
            throw new HttpException(502, 'Failed to initialize cURL for AI service.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->workerToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new HttpException(502, 'Cloudflare AI Request failed: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new HttpException(502, 'Cloudflare AI API returned HTTP status ' . $httpCode . ': ' . $response);
        }

        $data = json_decode((string)$response, true);
        if (!isset($data['success']) || !$data['success']) {
            $errMessage = isset($data['errors'][0]['message']) ? $data['errors'][0]['message'] : 'Unknown Cloudflare AI error';
            throw new HttpException(502, 'Cloudflare AI error: ' . $errMessage);
        }

        if (!isset($data['result']['response'])) {
            throw new HttpException(502, 'Invalid response structure from Cloudflare AI API.');
        }

        return (string)$data['result']['response'];
    }
}

<?php

namespace app\components;

use yii\base\Component;

class AiComponent extends Component
{
    public $accountId;
    public $apiToken;
    public $model;

    public function callAi($promt)
    {
        $url = sprintf('https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s', $this->accountId, $this->model);

        $body = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $promt,
                ]
            ]
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'error' => $error,
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'data' => json_decode($response, true),
        ];
    }
}

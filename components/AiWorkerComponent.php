<?php

namespace app\components;

use Exception;
use yii\base\Component;

class AiWorkerComponent extends Component
{
    public $accountId;
    public $apiToken;
    public $model;

    public function callAi($systemPrompt, $userPrompt)
    {
        $url = sprintf('https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s', $this->accountId, $this->model);

        $body = [
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception($err);
        }

        if ($httpCode != 200) {
            throw new Exception($response);
        }

        $result = json_decode($response, true);
        if (!isset($result['result']['response'])) {
            throw new Exception("Invail AI response: " . $response);
        }

        return $result['result']['response'];
    }

    public function generateTitle($description)
    {
        $systemPrompt = "Bạn là trợ lý viết blog chuyên nghiệp. Hãy gợi ý đúng 5 tiêu đề blog hấp dẫn, lôi cuốn dựa trên mô tả của người dùng. Trả về kết quả ngắn gọn dạng danh sách gạch đầu dòng, không thêm bất kỳ câu dẫn hay giải thích nào khác.";
        $response = $this->callAi($systemPrompt, $description);
        return $this->parseList($response, 7);
    }

    public function generateSummary($content)
    {
        $systemPrompt = "Bạn là trợ lý biên tập blog. Hãy tóm tắt văn bản người dùng cung cấp một cách ngắn gọn, súc tích trong vòng 2 đến 3 câu và nêu bật được ý chính. Trả về kết quả không thêm bất kỳ câu dẫn hay giải thích nào khác.";
        $response = $this->callAi($systemPrompt, $content);
        return trim($response);
    }

    public function improveText($text)
    {
        $systemPrompt = "Bạn là biên tập viên blog chuyên nghiệp. Hãy viết lại đoạn văn của người dùng để nó trôi chảy hơn, cuốn hút hơn, sửa lỗi diễn đạt nhưng vẫn giữ nguyên ý chính ban đầu. Trả về kết quả không thêm bất kỳ câu dẫn hay giải thích nào khác.";
        $response = $this->callAi($systemPrompt, $text);
        return trim($response);
    }

    private function parseList(string $raw, int $limit = null)
    {
        $lines = explode("\n", $raw);
        $items = [];
        foreach ($lines as $line) {

            $cleaned = trim(preg_replace('/^[\s\-*\d\.]+/u', '', $line));
            if (!empty($cleaned)) {
                $items[] = $cleaned;
            }
        }
        return array_slice($items, 0, $limit);
    }
}

<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\AiLogBase;
use app\models\query\AiLogQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\HttpException;

/**
 * AiLog model extending AiLogBase.
 */
class AiLog extends AiLogBase
{
    private const PROMPTS = [
        'generate-title' => "You are a professional blog editor. Generate exactly 5 catchy and SEO-friendly blog titles based on the user's topic or content outline. Format the output as a clean numbered list. Output ONLY the numbered list. No introductions, no explanations.",
        'generate-summary' => "You are a professional blog assistant. Summarize the provided text. The summary must be significantly
         shorter than the original text and under 100 words. Output ONLY the summary directly. 
         No introductory phrasing (like 'Here is a summary') or conversational filler. 
         If the input text is already shorter than 50 words, just return it as is or slightly refined.",
        'improve-text' => "You are an expert copywriter. Improve the clarity, tone, flow, and grammar of the user's text. Respond ONLY with the improved text. Do not add any introduction, explanations, or quotes.",
    ];

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): AiLogQuery
    {
        return new AiLogQuery(static::class);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Generate 5 titles and parse into an array of suggestions.
     *
     * @param string $prompt
     * @return array
     * @throws HttpException
     */
    public static function generateTitle(string $prompt): array
    {
        $response = self::logAndRequest('generate-title', self::PROMPTS['generate-title'], $prompt);

        $suggestions = [];
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $cleaned = preg_replace('/^\d+[\.\)]\s*|^\-\s*/', '', $line);
            if (!empty($cleaned)) {
                $suggestions[] = trim($cleaned, " \t\n\r\0\x0B\"'");
            }
        }
        return $suggestions;
    }

    /**
     * Generate summary.
     *
     * @param string $prompt
     * @return string
     * @throws HttpException
     */
    public static function generateSummary(string $prompt): string
    {
        return self::logAndRequest('generate-summary', self::PROMPTS['generate-summary'], $prompt);
    }

    /**
     * Improve text.
     *
     * @param string $prompt
     * @return string
     * @throws HttpException
     */
    public static function improveText(string $prompt): string
    {
        return self::logAndRequest('improve-text', self::PROMPTS['improve-text'], $prompt);
    }

    /**
     * Execute Cloudflare AI Worker request and save log in ai_log table.
     */
    protected static function logAndRequest(string $action, string $systemPrompt, string $userPrompt): string
    {
        $startTime = microtime(true);
        $promptSize = strlen($userPrompt);

        $log = new self();
        $log->action = $action;
        $log->prompt_size = $promptSize;
        $log->status = 1;

        try {
            $responseContent = Yii::$app->aiWorker->generate($systemPrompt, $userPrompt);
            $log->response_size = strlen($responseContent);
        } catch (\Throwable $e) {
            $log->status = 0;
            $log->response_size = 0;
            Yii::error('AI Service Error: ' . $e->getMessage(), __METHOD__);
            throw new HttpException(502, 'AI Service Error: ' . $e->getMessage(), 0, $e);
        } finally {
            $endTime = microtime(true);
            $log->execution_time = round($endTime - $startTime, 4);
            $log->save(false);
        }

        return $responseContent;
    }
}

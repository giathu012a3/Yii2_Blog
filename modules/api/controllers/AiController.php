<?php

namespace app\modules\api\controllers;

use app\models\AiLog;
use app\modules\api\models\forms\AiForm;
use app\rbac\Permission;
use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;

class AiController extends BaseController
{
    const ACTION_GENERATE_TITLE = 'generateTitle';
    const ACTION_GENERATE_SUMMARY = 'generateSummary';
    const ACTION_IMPROVE_TEXT = 'improveText';

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'generate-title' => ['POST'],
                'generate-summary' => ['POST'],
                'improve-text' => ['POST'],
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['generate-title', 'generate-summary', 'improve-text'],
                    'roles' => [Permission::ADMIN_ACCESS, Permission::AUTHOR_ACCESS],
                ]
            ],
        ];
        return $behaviors;
    }

    public function actionGenerateTitle()
    {
        $model = new AiForm();
        $model->scenario = AiForm::SCENARIO_GENERATE_TITLE;
        $model->load($this->request->post(), '');
        if ($model->validate()) {
            $titles = $this->callAi(self::ACTION_GENERATE_TITLE, $model->description);
            return ['titles' => $titles];
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }

    public function actionGenerateSummary()
    {
        $model = new AiForm();
        $model->scenario = AiForm::SCENARIO_GENERATE_SUMMARY;
        $model->load($this->request->post(), '');
        if ($model->validate()) {
            $content = $this->callAi(self::ACTION_GENERATE_SUMMARY, $model->content);
            return ['summary' => $content];
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }

    public function actionImproveText()
    {
        $model = new AiForm();
        $model->scenario = AiForm::SCENARIO_IMPROVE_TEXT;
        $model->load($this->request->post(), '');
        if ($model->validate()) {
            $text = $this->callAi(self::ACTION_IMPROVE_TEXT, $model->text);
            return ['text' => $text];
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }

    private function callAi(string $action, string $prompt)
    {
        $startTime = microtime(true);
        $promptSize = mb_strlen($prompt, 'UTF-8');

        $actionMapping = [
            self::ACTION_GENERATE_TITLE => 'generate-title',
            self::ACTION_GENERATE_SUMMARY => 'generate-summary',
            self::ACTION_IMPROVE_TEXT => 'improve-text',
        ];
        $actionDbName = $actionMapping[$action] ?? $action;
        try {
            $result = Yii::$app->aiComponent->$action($prompt);

            if ($result === false || $result === null) {
                throw new \Exception("AI Component returned failure or empty response.");
            }

            $duration = (int)((microtime(true) - $startTime) * 1000);
            $responseStr = is_array($result) ? json_encode($result, JSON_UNESCAPED_UNICODE) : (string)$result;

            $this->saveLog($actionDbName, $promptSize, mb_strlen($responseStr, 'UTF-8'), AiLog::STATUS_SUCCESS, $duration);
            return $result;
        } catch (\Exception $e) {
            $duration = (int)((microtime(true) - $startTime) * 1000);
            $this->saveLog($actionDbName, $promptSize, 0, AiLog::STATUS_FAILED, $duration);
            Yii::error("AI {$action} failed: " . $e->getMessage(), 'ai');
            throw new HttpException(502, "Cloudflare Workers AI failed: " . $e->getMessage());
        }
    }

    private function saveLog(string $action, int $promptSize, int $responseSize, int $status, int $duration)
    {
        $log = new AiLog();
        $log->user_id = Yii::$app->user->id;
        $log->action = $action;
        $log->prompt_size = $promptSize;
        $log->response_size = $responseSize;
        $log->status = $status;
        $log->duration = $duration;
        $log->save(false);
    }
}

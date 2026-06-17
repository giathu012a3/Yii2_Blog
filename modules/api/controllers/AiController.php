<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\modules\api\models\forms\AiRequestForm;
use app\models\AiLog;
use Yii;

class AiController extends BaseApiController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['generate-title', 'generate-summary', 'improve-text'],
                    'roles' => ['author', 'admin'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * POST api/ai/generate-title
     */
    public function actionGenerateTitle()
    {
        $model = new AiRequestForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->validate()) {
            $suggestions = AiLog::generateTitle($model->prompt);
            return [
                'suggestions' => $suggestions,
            ];
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }

    /**
     * POST api/ai/generate-summary
     */
    public function actionGenerateSummary()
    {
        $model = new AiRequestForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->validate()) {
            $response = AiLog::generateSummary($model->prompt);
            return [
                'response' => $response,
            ];
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }

    /**
     * POST api/ai/improve-text
     */
    public function actionImproveText()
    {
        $model = new AiRequestForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->validate()) {
            $response = AiLog::improveText($model->prompt);
            return [
                'response' => $response,
            ];
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }
}

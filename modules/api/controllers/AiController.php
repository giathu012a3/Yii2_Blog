<?php

namespace app\modules\api\controllers;

use app\modules\api\models\forms\AiForm;
use app\rbac\Permission;
use Yii;
use yii\filters\AccessControl;

class AiController extends BaseController
{
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
            $titles = Yii::$app->aiWorkerComponent->generateTitle($model->description);
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
            $summary = Yii::$app->aiWorkerComponent->generateSummary($model->content);
            return ['summary' => $summary];
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
            $text = Yii::$app->aiWorkerComponent->improveText($model->text);
            return ['text' => $text];
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }
}

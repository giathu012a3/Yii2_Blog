<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Post;
use app\modules\api\models\forms\PostForm;
use app\modules\api\models\search\PostSearch;
use yii\filters\AccessControl;
use Yii;

class PostController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => ['createPost'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new PostSearch();
        return $searchModel->search(Yii::$app->request->getQueryParams(), '');
    }

    public function actionCreate()
    {
        $model = new PostForm();

        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }
}

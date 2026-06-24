<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Tag;
use app\modules\api\models\forms\TagForm;
use app\modules\api\models\search\TagSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use Yii;

class TagController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['manageTags'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new TagSearch();
        return $searchModel->search(Yii::$app->request->getQueryParams(), '');
    }

    public function actionView($id)
    {
        $tag = Tag::find()->active()->andWhere(['id' => $id])->one();

        if ($tag === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Tag not found.'));
        }

        return $tag;
    }

    public function actionCreate()
    {
        $model = new TagForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }

    public function actionUpdate($id)
    {
        $model = TagForm::find()->active()->andWhere(['id' => $id])->one();

        if ($model === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Tag not found.'));
        }

        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->save()) {
            return $model;
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }

    public function actionDelete($id)
    {
        $tag = Tag::find()->active()->andWhere(['id' => $id])->one();

        if ($tag === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Tag not found.'));
        }

        if ($tag->softDelete()) {
            return ['message' => \Yii::t('app', 'Tag deleted successfully.')];
        }

        Yii::$app->response->statusCode = 500;
        return ['message' => \Yii::t('app', 'Failed to delete tag.')];
    }
}

<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Category;
use app\modules\api\models\forms\CategoryForm;
use app\modules\api\models\search\CategorySearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use Yii;

class CategoryController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['optional'] = ['index', 'view'];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'actions' => ['index', 'view'],
                    'allow' => true,
                ],
                [
                    'actions' => ['create', 'update', 'delete'],
                    'allow' => true,
                    'roles' => ['manageCategory'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        return $searchModel->search(Yii::$app->request->getQueryParams(), '');
    }

    public function actionView($id)
    {
        $category = Category::find()->active()->andWhere(['id' => $id])->one();

        if ($category === null) {
            throw new NotFoundHttpException('Category not found.');
        }

        return $category;
    }

    public function actionCreate()
    {
        $model = new CategoryForm();
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
        $model = CategoryForm::find()->active()->andWhere(['id' => $id])->one();

        if ($model === null) {
            throw new NotFoundHttpException('Category not found.');
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
        $category = Category::find()->active()->andWhere(['id' => $id])->one();

        if ($category === null) {
            throw new NotFoundHttpException('Category not found.');
        }

        if ($category->softDelete()) {
            return [
                'message' => 'Category deleted successfully.'
            ];
        }

        Yii::$app->response->statusCode = 500;
        return [
            'message' => 'Failed to delete category.'
        ];
    }
}


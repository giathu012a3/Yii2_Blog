<?php

namespace app\modules\api\controllers;

use app\models\Post;
use app\modules\api\models\forms\PostForm;
use app\modules\api\models\search\PostSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['optional'] = ['index', 'view'];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete'],
                    'roles' => ['@'],
                ]
            ]
        ];
        return $behaviors;
    }

    /**
     * Lists all Post models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $dataProvider;
    }

    /**
     * Displays a single Post model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== Post::STATUS_PUBLISHED) {
            if (Yii::$app->user->isGuest || (!Yii::$app->user->can('updatePost') && !Yii::$app->user->can('updateOwnPost', ['post' => $model]))) {
                throw new ForbiddenHttpException('You do not have permission to view this post.');
            }
        }
        $model->updateCounters(['view_count' => 1]);
        return $model;
    }

    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('createPost')) {
            throw new ForbiddenHttpException('You do not have permission to create a post.');
        }
        $model = new PostForm();
        $model->load($this->request->post(), '');
        //dd($model->tag_list);

        if ($model->save()) {
            return $model;
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;

        return $model->errors;
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('updatePost') && !Yii::$app->user->can('updateOwnPost', ['post' => $model])) {
            throw new ForbiddenHttpException('You do not have permission to update this post.');
        }

        $model->load($this->request->post(), '');

        if ($model->save()) {
            return $model;
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;

        return $model->errors;
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('deletePost') && !Yii::$app->user->can('deleteOwnPost', ['post' => $model])) {
            throw new ForbiddenHttpException('You do not have permission to delete this post.');
        }

        if ($model->softDelete()) {
            return [
                'message' => 'Post deleted successfully.',
            ];
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return [
            'message' => 'Failed to delete the post.',
        ];
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $query = PostForm::find()->where(['id' => $id]);
        if (!Yii::$app->user->can('updatePost')) {
            $query->notDelete();
        }
        $model = $query->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

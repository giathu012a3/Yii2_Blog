<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Post;
use app\modules\api\models\forms\PostForm;
use app\modules\api\models\search\PostSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use Yii;

class PostController extends BaseApiController
{
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
                    'roles' => ['?', '@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create', 'manage', 'manage-list', 'update', 'delete', 'publish'],
                    'roles' => ['createPost'],
                ],
                [
                    'allow' => true,
                    'actions' => ['like'],
                    'roles' => ['likePost'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $searchModel->isManagement = false;
        return $searchModel->search(Yii::$app->request->getQueryParams(), '');
    }

    public function actionManageList()
    {
        $searchModel = new PostSearch();
        $searchModel->isManagement = true;
        return $searchModel->search(Yii::$app->request->getQueryParams(), '');
    }

    public function actionView($slug)
    {
        $post = Post::find()->active()->published()->bySlug($slug)->one();
        if ($post === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        $post->incrementViewCount();

        return $post;
    }

    public function actionManage($id)
    {
        $post = Post::find()->active()->byId((int)$id)->one();
        if ($post === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        if (!Yii::$app->user->can('updatePost', ['model' => $post])) {
            throw new ForbiddenHttpException('You are not allowed to manage this post.');
        }

        return $post;
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

    public function actionUpdate($id)
    {
        $model = PostForm::find()->active()->byId((int)$id)->one();
        if ($model === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        if (!Yii::$app->user->can('updatePost', ['model' => $model])) {
            throw new ForbiddenHttpException('You are not allowed to update this post.');
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
        $post = Post::find()->active()->byId((int)$id)->one();
        if ($post === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        if (!Yii::$app->user->can('deletePost', ['model' => $post])) {
            throw new ForbiddenHttpException('You are not allowed to delete this post.');
        }

        if ($post->softDelete()) {
            Yii::$app->response->statusCode = 204;
            return null;
        }

        throw new ServerErrorHttpException('Failed to delete post.');
    }

    public function actionLike($id)
    {
        $post = Post::find()->active()->published()->byId((int)$id)->one();
        if ($post === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        return $post->toggleLike((int) Yii::$app->user->id);
    }

    public function actionPublish($id)
    {
        $post = Post::find()->active()->byId((int)$id)->one();
        if ($post === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        if (!Yii::$app->user->can('updatePost', ['model' => $post])) {
            throw new ForbiddenHttpException('You are not allowed to publish this post.');
        }

        $post->status = Post::STATUS_PUBLISHED;
        if ($post->published_at === null) {
            $post->published_at = time();
        }

        if ($post->save(false)) {
            return $post;
        }

        throw new ServerErrorHttpException('Failed to publish post.');
    }
}

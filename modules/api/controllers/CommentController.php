<?php

namespace app\modules\api\controllers;

use Yii;
use app\models\Comment;
use app\modules\api\models\forms\CommentForm;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

class CommentController extends BaseController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'hide', 'delete'],
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionCreate($post_id)
    {
        $model = new CommentForm();
        $model->post_id = $post_id;
        $model->author_id = Yii::$app->user->id;
        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return [
                'message' => 'Comment added successfully.',
                'comment' => $model,
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $isCommentOwner = (Yii::$app->user->id === $model->author_id);
        $isAdmin = Yii::$app->user->can('manageCategories');

        if (!$isCommentOwner && !$isAdmin) {
            throw new ForbiddenHttpException('You do not have permission to edit this comment.');
        }

        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return [
                'message' => 'Comment added successfully.',
                'comment' => $model,
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }

    public function actionHide($id)
    {
        $model = $this->findModel($id);

        $post = $model->post;
        if (!$post) {
            throw new NotFoundHttpException('Post associated with this comment not found.');
        }

        $isPostOwner = (Yii::$app->user->id === $post->author_id);
        $isAdmin = Yii::$app->user->can('manageCategories');

        if (!$isPostOwner && !$isAdmin) {
            throw new ForbiddenHttpException('You do not have permission to hide this comment.');
        }

        $model->status = Comment::STATUS_INACTIVE;
        if ($model->save(false)) {
            return [
                'message' => 'Comment has been hidden successfully.',
                'comment' => $model,
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_INTERNAL_SERVER_ERROR;
        return [
            'message' => 'Failed to hide the comment.',
        ];
    }


    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $post = $model->post;

        $isCommentOwner = (Yii::$app->user->id === $model->author_id);
        $isPostOwner = ($post && Yii::$app->user->id === $post->author_id);
        $isAdmin = Yii::$app->user->can('manageCategories');

        if (!$isCommentOwner && !$isPostOwner && !$isAdmin) {
            throw new ForbiddenHttpException('You do not have permission to delete this comment.');
        }

        if ($model->delete()) {
            return [
                'message' => 'Comment deleted successfully.',
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_INTERNAL_SERVER_ERROR;
        return [
            'message' => 'Failed to delete the comment.',
        ];
    }

    protected function findModel($id)
    {
        if (($model = CommentForm::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

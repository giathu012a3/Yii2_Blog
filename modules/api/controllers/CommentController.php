<?php

namespace app\modules\api\controllers;

use Yii;
use app\models\Comment;
use app\modules\api\models\forms\CommentForm;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

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

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->status = Comment::STATUS_INACTIVE;
            if ($model->save(false)) {
                Comment::updateAll(['status' => Comment::STATUS_INACTIVE], ['parent_id' => $model->id]);
                $transaction->commit();
                return [
                    'message' => 'Comment and its replies have been hidden successfully.',
                    'comment' => $model,
                ];
            }
            throw new \Exception('Failed to hide comment.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new ServerErrorHttpException($e->getMessage());
        }
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

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            Comment::deleteAll(['parent_id' => $model->id]);
            if ($model->delete()) {
                $transaction->commit();
                return [
                    'message' => 'Comment and its replies deleted successfully.',
                ];
            }
            throw new \Exception('Failed to delete comment.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new ServerErrorHttpException($e->getMessage());
        }
    }


    protected function findModel($id)
    {
        if (($model = CommentForm::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Comment;
use app\models\Post;
use app\modules\api\models\forms\CommentForm;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use Yii;

class CommentController extends BaseApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['optional'] = ['index'];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index'],
                    'roles'   => ['?', '@'],
                ],
                [
                    'allow'   => true,
                    'actions' => ['create', 'update', 'delete'],
                    'roles'   => ['createComment'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex(int $postId): array
    {
        if (!Post::find()->active()->published()->byId($postId)->exists()) {
            throw new NotFoundHttpException(\Yii::t('app', 'Post not found.'));
        }

        return Comment::find()->threadedByPost($postId)->all();
    }

    public function actionCreate(int $postId): CommentForm|array
    {
        if (!Post::find()->active()->published()->byId($postId)->exists()) {
            throw new NotFoundHttpException(\Yii::t('app', 'Post not found.'));
        }

        $form = new CommentForm();
        $form->load(Yii::$app->request->getBodyParams(), '');
        $form->post_id = $postId;

        if ($form->save()) {
            Yii::$app->response->statusCode = 201;
            return $form;
        }

        Yii::$app->response->statusCode = 422;
        return $form->getErrors();
    }

    public function actionUpdate(int $id): CommentForm|array
    {
        $form = CommentForm::find()->active()->byId($id)->one();
        if ($form === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Comment not found.'));
        }

        if (!Yii::$app->user->can('updateComment', ['model' => $form])) {
            throw new ForbiddenHttpException(\Yii::t('app', 'You are not allowed to update this comment.'));
        }

        $form->load(Yii::$app->request->getBodyParams(), '');

        if ($form->save()) {
            return $form;
        }

        Yii::$app->response->statusCode = 422;
        return $form->getErrors();
    }

    public function actionDelete(int $id): void
    {
        $comment = Comment::find()->active()->byId($id)->one();

        if ($comment === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Comment not found.'));
        }

        if (!Yii::$app->user->can('deleteComment', ['model' => $comment])) {
            throw new ForbiddenHttpException(\Yii::t('app', 'You are not allowed to delete this comment.'));
        }

        if ($comment->softDelete()) {
            Yii::$app->response->statusCode = 204;
            return;
        }

        throw new ServerErrorHttpException(\Yii::t('app', 'Failed to delete comment.'));
    }
}

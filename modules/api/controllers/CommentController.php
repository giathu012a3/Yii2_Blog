<?php

namespace app\modules\api\controllers;

use app\models\Comment;
use app\modules\api\models\forms\CommentForm;
use app\rbac\Permission;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class CommentController extends BaseController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'hide' => ['POST'],
                'delete' => ['DELETE'],
            ],
        ];
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
        $model->load(Yii::$app->request->post(), '');
        $model->post_id = (int)$post_id;
        $model->author_id = Yii::$app->user->id;

        if ($model->save()) {
            return [
                'message' => Yii::t('app', 'Comment added successfully.'),
                'comment' => $model,
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can(Permission::ADMIN_ACCESS) && !Yii::$app->user->can(Permission::UPDATE_OWN_COMMENT, ['comment' => $model])) {
            throw new ForbiddenHttpException(Yii::t('app', 'You do not have permission to edit this comment.'));
        }

        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return [
                'message' => Yii::t('app', 'Comment updated successfully.'),
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
            throw new NotFoundHttpException(Yii::t('app', 'Post associated with this comment not found.'));
        }

        if (!Yii::$app->user->can(Permission::ADMIN_ACCESS) && !Yii::$app->user->can(Permission::HIDE_COMMENT_ON_OWN_POST, ['post' => $post])) {
            throw new ForbiddenHttpException(Yii::t('app', 'You do not have permission to hide this comment.'));
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->status = Comment::STATUS_INACTIVE;
            if ($model->save(false)) {
                Comment::updateAll(['status' => Comment::STATUS_INACTIVE], ['parent_id' => $model->id]);
                $transaction->commit();
                return [
                    'message' => Yii::t('app', 'Comment and its replies have been hidden successfully.'),
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

        if (!Yii::$app->user->can(Permission::ADMIN_ACCESS) &&
            !Yii::$app->user->can(Permission::DELETE_OWN_COMMENT, ['comment' => $model]) &&
            !Yii::$app->user->can(Permission::DELETE_COMMENT_ON_OWN_POST, ['post' => $post])
        ) {
            throw new ForbiddenHttpException(Yii::t('app', 'You do not have permission to delete this comment.'));
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            Comment::deleteAll(['parent_id' => $model->id]);
            if ($model->delete()) {
                $transaction->commit();
                return [
                    'message' => Yii::t('app', 'Comment and its replies deleted successfully.'),
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

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Media;
use app\modules\api\models\forms\UploadForm;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use Yii;

class MediaController extends BaseApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['createPost'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionUpload()
    {
        $form = new UploadForm();
        $form->file = UploadedFile::getInstanceByName('file');

        if (!$form->validate()) {
            Yii::$app->response->statusCode = 422;
            return $form->getErrors();
        }

        $media = new Media();
        $media->setUploadedFile($form->file);

        if (!$media->validate()) {
            Yii::$app->response->statusCode = 422;
            return $media->getErrors();
        }

        $dbTrans = Yii::$app->db->beginTransaction();
        try {
            if ($media->save(false)) {
                $dbTrans->commit();
                Yii::$app->response->statusCode = 201;
                return [
                    'media_id' => $media->id,
                    'file_url' => $media->file_url,
                ];
            }
            $dbTrans->rollBack();
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            if (!empty($media->file_name)) {
                try {
                    Yii::$app->r2->delete($media->file_name);
                } catch (\Throwable $cleanupEx) {
                    Yii::error("Failed to clean up orphaned R2 file: " . $cleanupEx->getMessage());
                }
            }
            Yii::$app->response->statusCode = 502;
            return [
                'message' => 'Failed to save media metadata or upload file to R2: ' . $e->getMessage()
            ];
        }

        Yii::$app->response->statusCode = 502;
        return [
            'message' => 'Failed to upload file to Cloudflare R2.'
        ];
    }

    public function actionDelete($id)
    {
        $media = Media::findOne($id);
        if ($media === null) {
            throw new NotFoundHttpException('Media not found.');
        }

        if ($media->user_id !== (int)Yii::$app->user->id && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('You are not allowed to delete this media.');
        }

        if ($media->delete()) {
            Yii::$app->response->statusCode = 204;
            return null;
        }

        throw new ServerErrorHttpException('Failed to delete media.');
    }
}

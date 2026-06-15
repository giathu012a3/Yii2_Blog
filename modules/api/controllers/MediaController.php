<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Media;
use app\modules\api\models\forms\UploadForm;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
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
        $media->user_id = Yii::$app->user->id;
        $media->mime_type = $form->file->type;
        $media->size = $form->file->size;
        $media->setUploadedFile($form->file);

        if ($media->save(false)) {
            Yii::$app->response->statusCode = 201;
            return [
                'media_id' => $media->id,
                'file_url' => $media->file_url,
            ];
        }

        Yii::$app->response->statusCode = 500;
        return [
            'message' => 'Failed to save media metadata.'
        ];
    }
}

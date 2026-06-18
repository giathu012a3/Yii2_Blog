<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\UploadedFile;
use app\modules\api\models\forms\UploadForm;
use app\models\Media;
use app\rbac\Permission;
use yii\filters\AccessControl;

class MediaController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'upload' => ['POST'],
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['upload'],
                    'roles' => [Permission::AUTHOR_ACCESS, Permission::ADMIN_ACCESS],
                ]
            ]
        ];
        return $behaviors;
    }

    public function actionUpload()
    {
        $model = new UploadForm();
        $model->files = UploadedFile::getInstancesByName('files');

        if ($model->validate()) {
            $uploads = [];
            foreach ($model->files as $file) {
                $media = Media::uploadAndCreate($file, 'content');
                if ($media) {
                  $uploads[] = [
                    'url' => $media->url,
                    'id' => $media->id
                  ];
                }
            }

            if (empty($uploads)) {
                Yii::$app->response->statusCode = self::HTTP_INTERNAL_SERVER_ERROR;
                return [
                    'message' => 'Failed to upload files.',
                ];
            }

            return [
                'upload' => $uploads,
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return $model->errors;
    }
}

<?php

namespace app\modules\api\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\rest\Serializer;

class BaseController extends Controller
{
    protected const HTTP_OK = 200;
    protected const HTTP_CREATED = 201;
    protected const HTTP_NO_CONTENT = 204;

    protected const HTTP_BAD_REQUEST = 400;
    protected const HTTP_UNAUTHORIZED = 401;
    protected const HTTP_FORBIDDEN = 403;
    protected const HTTP_NOT_FOUND = 404;
    protected const HTTP_UNPROCESSABLE_ENTITY = 422;

    protected const HTTP_INTERNAL_SERVER_ERROR = 500;

    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items'
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        return  $behaviors;
    }
}

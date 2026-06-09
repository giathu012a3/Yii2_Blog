<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;

/**
 * BaseApiController is the base class for all REST API controllers in the api module.
 * It enforces stateless Bearer token authentication by default.
 */
class BaseApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        return $behaviors;
    }
}

<?php

declare(strict_types=1);

namespace app\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;

/**
 * BaseApiController is the base class for all REST API controllers in this application.
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

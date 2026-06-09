<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\User;
use Yii;

/**
 * AuthController handles user authentication actions like register, login, and logout.
 */
class AuthController extends BaseApiController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['optional'] = ['register', 'login'];

        return $behaviors;
    }

    /**
     * Register a new user account.
     *
     * @return array
     */
    public function actionRegister()
    {
        $model = new RegisterForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($user = $model->register()) {
            Yii::$app->response->statusCode = 201;
            return $user;
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }

    /**
     * Authenticate user credentials and return bearer token.
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($user = $model->login()) {
            return $user->toArray(['id', 'username', 'email'], ['access_token']);
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }

    /**
     * Log out the current authenticated user by revoking their access token.
     * 
     */
    public function actionLogout()
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        if ($user) {
            $user->revokeAccessToken();
            if ($user->save(false)) {
                return [
                    'message' => 'Logged out successfully.',
                ];
            }
        }

        Yii::$app->response->statusCode = 500;
        return [
            'message' => 'Failed to logout.',
        ];
    }
}

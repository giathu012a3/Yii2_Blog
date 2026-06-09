<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\forms\ChangePasswordForm;
use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\User;
use Yii;

/**
 * AuthController handles user authentication actions like register, login, me, change-password, and logout.
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
     * Get the profile of the current authenticated user.
     */
    public function actionMe()
    {
        return Yii::$app->user->identity;
    }

    /**
     * Log out the current authenticated user by revoking their access token.
     */
    public function actionLogout()
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        if ($user instanceof User) {
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

    /**
     * Change password of the current authenticated user.
     */
    public function actionChangePassword()
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        if ($user instanceof User) {
            $model = new ChangePasswordForm($user);
            $model->load(Yii::$app->request->getBodyParams(), '');

            if ($model->change()) {
                return [
                    'message' => 'Password changed successfully. Please login again with your new password.',
                ];
            }

            Yii::$app->response->statusCode = 422;
            return $model->getErrors();
        }

        Yii::$app->response->statusCode = 401;
        return [
            'message' => 'Unauthorized.',
        ];
    }
}

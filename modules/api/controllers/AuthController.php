<?php

namespace app\modules\api\controllers;

use app\behaviors\LoginRateLimiter;
use app\modules\api\models\forms\LoginForm;
use app\modules\api\models\forms\RegisterForm;
use Yii;

class AuthController extends BaseController
{
    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'register' => ['POST'],
                'login' => ['POST'],
                'logout' => ['POST'],
                'me' => ['GET'],
            ],
        ];
        $behaviors['authenticator']['optional'] = ['register', 'login'];
        $behaviors['rateLimiter'] = [
            'class' => LoginRateLimiter::class,
            'only' => ['login'],
        ];
        return $behaviors;
    }

    public function actionRegister()
    {
        $form = new RegisterForm();
        $form->load(Yii::$app->request->post(), '');
        $user = $form->register();
        if ($user) {
            return $user;
        }
        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return [
            'errors' => $form->errors
        ];
    }

    public function actionLogin()
    {
        $form = new LoginForm();
        $form->load(Yii::$app->request->post(), '');
        $user = $form->login();
        if ($user) {
            return [
                'user' => $user,
                'access_token' => $user->currentToken->token,
            ];
        }

        Yii::$app->response->statusCode = self::HTTP_UNPROCESSABLE_ENTITY;
        return [
            'errors' => $form->errors
        ];
    }

    public function actionLogout()
    {
        $user = Yii::$app->user->identity;

        if ($user->currentToken) {
            $user->currentToken->updateAttributes([
                'revoked_at' => time(),
            ]);
        }

        return [
            'message' => Yii::t('app','Logout successfully.'),
        ];
    }

    public function actionMe()
    {
        return Yii::$app->user->identity;
    }
}

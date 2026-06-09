<?php

namespace app\modules\api\controllers;

use app\modules\api\models\forms\RegisterForm;
use Yii;

class AuthController extends BaseController
{

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        $behaviors['authenticator']['optional'] = ['register'];
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

}

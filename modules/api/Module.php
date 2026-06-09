<?php

namespace app\modules\api;

use Yii;
use yii\web\Response;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\api\controllers';

    public function init()
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->user->enableSession = false;
    }
}

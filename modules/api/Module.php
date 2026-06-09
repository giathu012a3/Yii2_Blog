<?php

declare(strict_types=1);

namespace app\modules\api;

/**
 * api module definition class.
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Ensure stateless REST API behavior by disabling session in user component
        \Yii::$app->user->enableSession = false;
    }
}

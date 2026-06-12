<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', isset($_ENV['YII_DEBUG']) ? filter_var($_ENV['YII_DEBUG'], FILTER_VALIDATE_BOOLEAN) : true);
defined('YII_ENV') or define('YII_ENV', isset($_ENV['YII_ENV']) ? strtolower($_ENV['YII_ENV']) : 'dev');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();

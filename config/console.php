<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$cacheDriver = $_ENV['CACHE_DRIVER'] ?? 'file';
$cacheConfig = [
    'class' => \yii\caching\FileCache::class,
];

if ($cacheDriver === 'redis') {
    $cacheConfig = [
        'class' => \yii\redis\Cache::class,
        'redis' => 'redis'
    ];
}

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'useFileTransport' => ($_ENV['MAIL_FILE_TRANSPORT'] ?? 'true') === 'true',
                'viewPath' => '@app/mail',
                'htmlLayout' => false,
                'textLayout' => false,
                'transport' => [
                    'scheme' => 'smtp',
                    'host' => $_ENV['SMTP_HOST'] ?? '',
                    'username' => $_ENV['SMTP_USER'] ?? '',
                    'password' => $_ENV['SMTP_PASS'] ?? '',
                    'port' => (int)($_ENV['SMTP_PORT'] ?? 2525),
                    'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
                ],
                'messageConfig' => [
                    'from' => [
                        ($_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@example.com') => ($_ENV['MAIL_FROM_NAME'] ?? 'Yii2 Blog App')
                    ],
                ]
            ],
        ],
    ],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => $cacheConfig,
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
            'cache' => 'cache',
        ],
        'redis' => [
            'class' => \yii\redis\Connection::class,
            'hostname' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['REDIS_PORT'] ?? 6379,
            'database' => $_ENV['REDIS_DATABASE'] ?? 0,
            'password' => !empty($_ENV['REDIS_PASSWORD']) ? $_ENV['REDIS_PASSWORD'] : null,
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],

    ],
    'params' => $params,
    'controllerMap' => [
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                '@app/migrations',
                '@yii/rbac/migrations',
            ],
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
    // configuration adjustments for 'dev' environment
    // requires version `2.1.21` of yii2-debug module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;

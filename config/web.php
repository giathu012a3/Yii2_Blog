<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'api' => [
            'class' => \app\modules\api\Module::class,
        ],
    ],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'] ?? '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass'   => \app\models\User::class,
            'enableAutoLogin' => false,
            'enableSession'   => false,
            'loginUrl'        => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db'          => $db,
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
            'cache' => 'cache',
        ],
        'response'    => [
            'format'  => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
                $response = $event->sender;

                if ($response->format !== \yii\web\Response::FORMAT_JSON) {
                    return;
                }

                $isSuccessful = $response->isSuccessful;
                $code         = $response->statusCode;
                $data         = $response->data;

                $status  = $isSuccessful ? 'success' : 'error';
                $message = $isSuccessful ? 'Success' : 'An error occurred';
                $responseData = $data;

                if (!$isSuccessful) {
                    if (is_array($data) && isset($data['message'])) {
                        $message = $data['message'];
                    }

                    $responseData = ($code === 422) ? $data : null;
                }

                $response->data = [
                    'status'  => $status,
                    'code'    => $code,
                    'message' => $message,
                    'data'    => $responseData,
                ];
            },
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules' => [
                'GET  api/auth/me'              => 'api/auth/me',
                'POST api/auth/register'        => 'api/auth/register',
                'POST api/auth/login'           => 'api/auth/login',
                'POST api/auth/logout'          => 'api/auth/logout',
                'PUT  api/auth/change-password' => 'api/auth/change-password',
                'GET  api/categories'           => 'api/category/index',
                'GET  api/categories/<id:\d+>'  => 'api/category/view',
                'POST api/categories'           => 'api/category/create',
                'PUT  api/categories/<id:\d+>'  => 'api/category/update',
                'DELETE api/categories/<id:\d+>' => 'api/category/delete',
                'GET  api/tags'                 => 'api/tag/index',
                'GET  api/tags/<id:\d+>'        => 'api/tag/view',
                'POST api/tags'                 => 'api/tag/create',
                'PUT  api/tags/<id:\d+>'        => 'api/tag/update',
                'DELETE api/tags/<id:\d+>'      => 'api/tag/delete',
                'GET  api/posts/manage'         => 'api/post/manage-list',
                'GET  api/posts/<id:\d+>/manage' => 'api/post/manage',
                'GET  api/posts'                => 'api/post/index',
                'GET  api/posts/<slug:[a-zA-Z0-9\-]+>' => 'api/post/view',
                'POST api/posts'                => 'api/post/create',
                'PUT  api/posts/<id:\d+>'       => 'api/post/update',
                'DELETE api/posts/<id:\d+>'     => 'api/post/delete',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;

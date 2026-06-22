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
        'formatter' => [
            'class'          => \yii\i18n\Formatter::class,
            'datetimeFormat' => 'php:d/m/Y H:i:s',
            'dateFormat'     => 'php:d/m/Y',
            'timeFormat'     => 'php:H:i:s',
            'nullDisplay'    => null,
        ],
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
        'r2'          => [
            'class' => \app\components\R2Component::class,
            'accountId' => $_ENV['CF_ACCOUNT_ID'] ?? '',
            'accessKeyId' => $_ENV['R2_ACCESS_KEY'] ?? '',
            'secretAccessKey' => $_ENV['R2_SECRET_KEY'] ?? '',
            'bucketName' => $_ENV['R2_BUCKET'] ?? '',
            'publicUrl' => $_ENV['R2_PUBLIC_URL'] ?? '',
        ],
        'aiWorker' => [
            'class' => \app\components\AiWorkerComponent::class,
            'accountId' => $_ENV['CF_ACCOUNT_ID'] ?? '',
            'workerToken' => $_ENV['AI_WORKER_TOKEN'] ?? '',
            'model' => $_ENV['AI_WORKER_MODEL'] ?? '@cf/meta/llama-3.1-8b-instruct',
            'workerUrl' => $_ENV['AI_WORKER_URL'] ?? '',
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

                if ($isSuccessful) {
                    if (is_array($data) && isset($data['items'], $data['pagination'])) {
                        $pag = $data['pagination'];
                        $responseData = [
                            'items' => $data['items'],
                            'pagination' => [
                                'total'      => isset($pag['totalCount']) ? (int)$pag['totalCount'] : 0,
                                'page'       => isset($pag['currentPage']) ? (int)$pag['currentPage'] : 1,
                                'limit'      => isset($pag['perPage']) ? (int)$pag['perPage'] : 10,
                                'total_page' => isset($pag['pageCount']) ? (int)$pag['pageCount'] : 0,
                            ],
                        ];
                    }
                } else {
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
                [
                    'class'      => \yii\rest\UrlRule::class,
                    'controller' => [
                        'api/categories' => 'api/category',
                        'api/tags'       => 'api/tag',
                        'api/comments'   => 'api/comment',
                    ],
                    'pluralize'  => false,
                ],
                [
                    'class'         => \yii\rest\UrlRule::class,
                    'controller'    => ['api/posts' => 'api/post'],
                    'pluralize'     => false,
                    'tokens'        => [
                        '{id}'   => '<id:\d+>',
                        '{slug}' => '<slug:[a-zA-Z0-9\-]+>',
                    ],
                    'extraPatterns' => [
                        'GET manage'      => 'manage-list',
                        'GET {id}/manage' => 'manage',
                        'POST {id}/like'  => 'like',
                        'POST {id}/publish' => 'publish',
                        'GET {slug}'      => 'view',
                    ],
                ],
                'GET  api/posts/<postId:\d+>/comments' => 'api/comment/index',
                'POST api/posts/<postId:\d+>/comments' => 'api/comment/create',
                'POST api/media'                       => 'api/media/upload',
                'DELETE api/media/<id:\d+>'            => 'api/media/delete',
                'POST api/ai/generate-title'           => 'api/ai/generate-title',
                'POST api/ai/generate-summary'         => 'api/ai/generate-summary',
                'POST api/ai/improve-text'             => 'api/ai/improve-text',
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

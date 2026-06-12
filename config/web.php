<?php

use yii\web\Response;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
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
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser'
            ]
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
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
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // auth
                'POST api/auth/register' => 'api/auth/register',
                'POST api/auth/login' => 'api/auth/login',
                'POST api/auth/logout' => 'api/auth/logout',
                'GET api/auth/me' => 'api/auth/me',

                // admin
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/categories' => 'api/category',
                        'api/tags'       => 'api/tag',
                        'api/posts'      => 'api/post',
                    ],
                    'pluralize' => false,
                ],

                // comments
                'POST api/posts/<post_id:\d+>/comments' => 'api/comment/create',
                'PUT api/comments/<id:\d+>'             => 'api/comment/update',
                'POST api/comments/<id:\d+>/hide'        => 'api/comment/hide',
                'DELETE api/comments/<id:\d+>'          => 'api/comment/delete',

                //like
                'POST api/posts/<post_id:\d+>/like'      => 'api/post/like',


            ],
        ],
        'response' => [
            'class' => Response::class,
            'format' => Response::FORMAT_JSON,
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $route = Yii::$app->requestedRoute;

                if ($route !== null && str_starts_with($route, 'api/')) {
                    $isSuccess = $response->isSuccessful;
                    $data = $response->data;
                    $meta = null;

                    if ($isSuccess && is_array($data)) {
                        if (isset($data['items']) && is_array($data['items'])) {
                            $meta = $data['_meta'] ?? null;
                            $data = $data['items'];
                        }
                    }

                    $message = $isSuccess ? 'Success' : ($response->statusText ?: 'Error');

                    if (!$isSuccess && is_array($data) && isset($data['message'])) {
                        $message = $data['message'];
                    }

                    $formatData = [
                        'status' => $isSuccess ? 'success' : 'error',
                        'code' => $response->statusCode,
                        'message' => $message,
                        'data' => !$isSuccess && isset($data['errors']) ? $data['errors'] : $data,
                    ];

                    if ($meta) {
                        $formatData['pagination'] = [
                            'total' => isset($meta['totalCount']) ? (int)$meta['totalCount'] : null,
                            'page' => isset($meta['currentPage']) ? (int)$meta['currentPage'] : null,
                            'limit' => isset($meta['perPage']) ? (int)$meta['perPage'] : null,
                            'total_page' => isset($meta['pageCount']) ? (int)$meta['pageCount'] : null,
                        ];
                    }

                    $response->data = $formatData;
                }
            }
        ],
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ]
    ],
    'params' => $params,
    'modules' => [
        'api' => [
            'class' => \app\modules\api\Module::class,
        ]
    ]
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

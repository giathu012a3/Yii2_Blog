<?php

declare(strict_types=1);

return [
    'class'    => \yii\db\Connection::class,
    'dsn'      => "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS']    ?? '',
    'charset'  => $_ENV['DB_CHARSET'] ?? 'utf8mb4',

    // Schema cache options (enable for production)
    //'enableSchemaCache'   => true,
    //'schemaCacheDuration' => 3600,
    //'schemaCache'         => 'cache',
];

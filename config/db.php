<?php

declare(strict_types=1);

$host    = $_ENV['DB_HOST'];
$port    = $_ENV['DB_PORT'];
$dbname  = $_ENV['DB_NAME'];
$user    = $_ENV['DB_USER'];
$pass    = $_ENV['DB_PASS']    ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

return [
    'class'    => \yii\db\Connection::class,
    'dsn'      => "mysql:host={$host};port={$port};dbname={$dbname}",
    'username' => $user,
    'password' => $pass,
    'charset'  => $charset,

    // Schema cache options (enable for production)
    'enableSchemaCache'   => true,
    'schemaCacheDuration' => 3600,
    'schemaCache'         => 'cache',
];

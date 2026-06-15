<?php

namespace app\components;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

class R2Component
{
    private S3Client $client;
    protected string $bucket;

    public function __construct()
    {
        $access_key = $_ENV['R2_ACCESS_KEY_ID'];
        $secret_key = $_ENV['R2_SECRET_ACCESS_KEY'];
        $endpoint = $_ENV['R2_ENDPOINT'];
        $this->bucket = $_ENV['R2_BUCKET_NAME'];

        $credentials = new Credentials($access_key, $secret_key);

        $options = [
            'region' => 'auto', // Required by SDK but not used by R2
            'endpoint' => $endpoint,
            'version' => 'latest',
            'credentials' => $credentials
        ];

        $this->client = new S3Client($options);
    }


}

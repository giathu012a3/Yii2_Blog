<?php

declare(strict_types=1);

namespace app\components;

use yii\base\Component;
use Aws\S3\S3Client;
use Yii;

class R2Component extends Component
{
    public string $accountId = '';
    public string $accessKeyId = '';
    public string $secretAccessKey = '';
    public string $bucketName = '';
    public string $publicUrl = '';

    private ?S3Client $_client = null;

    public function init()
    {
        parent::init();

        $accountId = $this->accountId;
        $accessKeyId = $this->accessKeyId;
        $secretAccessKey = $this->secretAccessKey;

        $endpoint = "https://{$accountId}.r2.cloudflarestorage.com";

        $this->_client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $accessKeyId,
                'secret' => $secretAccessKey,
            ],
            'use_path_style_endpoint' => true,
            'http' => [
                'timeout' => 30.0,
                'connect_timeout' => 10.0,
            ],
        ]);
    }

    public function testConnection(): bool
    {
        try {
            $this->_client->listObjectsV2([
                'Bucket' => $this->bucketName,
                'MaxKeys' => 1,
            ]);
            return true;
        } catch (\Throwable $e) {
            Yii::error("R2 connection test failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

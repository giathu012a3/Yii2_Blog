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

    public function init(): void
    {
        parent::init();

        if (
            empty($this->accountId) ||
            empty($this->accessKeyId) ||
            empty($this->secretAccessKey) ||
            empty($this->bucketName)
        ) {
            throw new \RuntimeException('Cloudflare R2 configuration is missing.');
        }

        $endpoint = "https://{$this->accountId}.r2.cloudflarestorage.com";

        $this->_client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $this->accessKeyId,
                'secret' => $this->secretAccessKey,
            ],
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

    public function upload(string $tempFilePath, string $fileName, string $mimeType): ?string
    {
        try {
            $this->_client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
                'SourceFile' => $tempFilePath,
                'ContentType' => $mimeType,
            ]);

            return rtrim($this->publicUrl, '/') . '/' . ltrim($fileName, '/');
        } catch (\Throwable $e) {
            Yii::error("Failed to upload file to R2: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}

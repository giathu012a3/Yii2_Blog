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
            !empty($this->accountId) &&
            !empty($this->accessKeyId) &&
            !empty($this->secretAccessKey) &&
            !empty($this->bucketName)
        ) {
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
    }

    public function upload(string $tempFilePath, string $fileName, string $mimeType): ?string
    {
        if ($this->_client === null) {
            Yii::error("R2 upload failed: S3Client is not configured.", __METHOD__);
            return null;
        }

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

    public function getPresignedUrl(string $fileName, string $expires = '+20 minutes'): ?string
    {
        if ($this->_client === null) {
            Yii::error("R2 presigned URL generation failed: S3Client is not configured.", __METHOD__);
            return null;
        }

        try {
            $cmd = $this->_client->getCommand('GetObject', [
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
            ]);

            $request = $this->_client->createPresignedRequest($cmd, $expires);

            return (string)$request->getUri();
        } catch (\Throwable $e) {
            Yii::error("Failed to generate presigned URL: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    public function delete(string $fileName): bool
    {
        if ($this->_client === null) {
            Yii::error("R2 delete failed: S3Client is not configured.", __METHOD__);
            return false;
        }

        try {
            $this->_client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
            ]);
            return true;
        } catch (\Throwable $e) {
            Yii::error("Failed to delete file from R2: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

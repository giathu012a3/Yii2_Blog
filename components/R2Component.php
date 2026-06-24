<?php

namespace app\components;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Yii;
use yii\base\Component;

class R2Component extends Component
{
    private S3Client $client;
    public $bucket;
    public $publicUrl;
    public $accessKey;
    public $secretKey;
    public $endPoint;



    public function init()
    {
        parent::init();
        $credentials = new Credentials($this->accessKey, $this->secretKey);

        $options = [
            'region' => 'auto', // Required by SDK but not used by R2
            'endpoint' => $this->endPoint,
            'version' => 'latest',
            'credentials' => $credentials
        ];

        $this->client = new S3Client($options);
    }

    public function getUrl($key)
    {
        return rtrim($this->publicUrl, '/') . '/' . ltrim($key, '/');
    }

    public function upload($file, $folder)
    {
        $r2Key = $folder . '/' . date('Y/m') . '/' . uniqid() . '.' . $file->extension;
        $options = [
            'Bucket'      => $this->bucket,
            'Key'         => $r2Key,
            'SourceFile'  => $file->tempName,
            'ContentType' => $file->type,
        ];

        try {
            $this->client->putObject($options);
        } catch (\Exception $e) {
            Yii::error("Lỗi upload R2: " . $e->getMessage());
            throw new \Exception("Upload file lên Cloudflare R2 thất bại.");
        }

        return $this->getUrl($r2Key);
    }


    public function delete(string $r2Key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $r2Key,
            ]);
            return true;
        } catch (\Exception $e) {
            Yii::error("Lỗi xóa file trên R2 (Key: {$r2Key}): " . $e->getMessage());
            return false;
        }
    }
}

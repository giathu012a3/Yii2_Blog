<?php

namespace app\models;

use app\models\base\BaseMedia;
use Yii;
use yii\behaviors\TimestampBehavior;

class Media extends BaseMedia
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ]
        ];
    }
    public function fields()
    {
        return [
            'id',
            'model_id',
            'model_name',
            'url',
            'collection',
            'file_size',
            'mime_type',
            'created_at',
        ];
    }

    public static function uploadAndCreate($file, $collection, $modelId = null, $modelName = null)
    {
        $r2 = Yii::$app->r2Component;

        $folder = $collection === 'thumbnail' ? 'thumbnails' : 'content';

        try {
            $url = $r2->upload($file, $folder);
            $publicUrl = Yii::$app->r2Component->publicUrl;
            $storageKey = str_replace(rtrim($publicUrl, '/') . '/', '', $url);
        } catch (\Exception $e) {
            Yii::error("R2 Upload failed: " . $e->getMessage(), 'media');
            return null;
        }
        $media = new self();
        $media->model_id = $modelId;
        $media->model_name = $modelName;
        $media->collection = $collection;
        $media->url = $url;
        $media->storage_key = $storageKey;
        $media->file_size = $file->size;
        $media->mime_type = $file->type;

        if (!$media->save()) {
            Yii::error("Failed to save media metadata: " . json_encode($media->errors), 'media');

            try {
                $r2->delete($storageKey);
            } catch (\Exception $ex) {
                Yii::error("Failed to clean up R2 file '{$storageKey}' after DB failure: " . $ex->getMessage(), 'media');
            }
            return null;
        }
        return $media;
    }

    public static function createFromUrl(string $url, string $collection, $modelId = null, $modelName = null): ?self
    {

        $existingMedia = self::findOne(['url' => $url]);
        $media = new self();
        $media->model_id = $modelId;
        $media->model_name = $modelName;
        $media->collection = $collection;
        $media->url = $url;

        if ($existingMedia) {
            $media->storage_key = $existingMedia->storage_key;
            $media->file_size = $existingMedia->file_size;
            $media->mime_type = $existingMedia->mime_type;
        } else {

            $media->storage_key = null;
            $media->file_size = null;
            $media->mime_type = null;
        }
        if (!$media->save()) {
            Yii::error("Failed to save media from URL: " . json_encode($media->errors), 'media');
            return null;
        }
        return $media;
    }

    public static function findAllImagesInContent(?string $content): array
    {
        if (empty($content)) {
            return [];
        }

        $urls = [];

        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }

        if (preg_match_all('/!\[[^\]]*]\(([^)]+)\)/', $content, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }

        $urls = array_map(
            static fn(string $url) => trim(html_entity_decode($url)),
            $urls
        );

        return array_values(array_unique(array_filter($urls)));
    }

    public static function findFirstImageInContent(?string $content): ?string
    {
        $images = self::findAllImagesInContent($content);
        return !empty($images) ? reset($images) : null;
    }

    public function deleteMedia(bool $deleteFromR2 = true)
    {
        if ($deleteFromR2 && !empty($this->storage_key)) {
            $otherReferencesCount = self::find()
                ->where(['storage_key' => $this->storage_key])
                ->andWhere(['not', ['id' => $this->id]])
                ->count();
            if ($otherReferencesCount == 0) {
                try {
                    $r2 = Yii::$app->r2Component;
                    $r2->delete($this->storage_key);
                } catch (\Exception $e) {
                    Yii::error("Failed to delete media from R2 (Key: {$this->storage_key}): " . $e->getMessage(), 'media');
                }
            }
        }
        return (bool)$this->delete();
    }
}

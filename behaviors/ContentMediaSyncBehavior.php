<?php

declare(strict_types=1);

namespace app\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\Media;
use app\models\MediaLink;
use Yii;

class ContentMediaSyncBehavior extends Behavior
{
    public string $contentAttribute = 'content';
    public string $modelType = 'Post';

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'syncContentMedia',
            ActiveRecord::EVENT_AFTER_UPDATE => 'syncContentMedia',
        ];
    }

    public function syncContentMedia($event)
    {
        $contentAttr = $this->contentAttribute;
        if (!$this->owner->canGetProperty($contentAttr) && !property_exists($this->owner, $contentAttr)) {
            return;
        }

        $insert = ($event->name === ActiveRecord::EVENT_AFTER_INSERT);

        if (!$insert) {
            MediaLink::deleteAll([
                'model_type' => $this->modelType,
                'model_id' => $this->owner->id,
                'group_type' => 'content',
            ]);
        }

        $publicUrl = rtrim(Yii::$app->r2->publicUrl ?? '', '/');
        if (empty($publicUrl)) {
            return;
        }

        $escapedUrl = preg_quote($publicUrl, '/');
        $pattern = '/' . $escapedUrl . '\/([a-zA-Z0-9_\-]{32}\.(?:png|jpg|jpeg|webp))/i';
        preg_match_all($pattern, (string)$this->owner->$contentAttr, $matches);
        $filenames = array_unique($matches[1] ?? []);

        if (empty($filenames)) {
            return;
        }

        $mediaList = Media::find()->andWhere(['file_name' => $filenames])->all();
        if (empty($mediaList)) {
            return;
        }

        $rows = [];
        foreach ($mediaList as $media) {
            $rows[] = [
                'media_id' => $media->id,
                'model_type' => $this->modelType,
                'model_id' => $this->owner->id,
                'group_type' => 'content',
            ];
        }

        Yii::$app->db->createCommand()
            ->batchInsert(
                MediaLink::tableName(),
                ['media_id', 'model_type', 'model_id', 'group_type'],
                $rows
            )
            ->execute();

        unset($this->owner->mediaLinks);
        unset($this->owner->media);
    }
}

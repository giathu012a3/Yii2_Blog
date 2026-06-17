<?php

declare(strict_types=1);

namespace app\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\MediaLink;
use app\models\Media;
use yii\base\InvalidConfigException;

class MediaLinkBehavior extends Behavior
{
    public array $attributes = [];
    public ?string $modelType = null;

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'autoDetectThumbnail',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'autoDetectThumbnail',
            ActiveRecord::EVENT_AFTER_INSERT => 'syncMediaLinks',
            ActiveRecord::EVENT_AFTER_UPDATE => 'syncMediaLinks',
        ];
    }

    public function init()
    {
        parent::init();
        if ($this->modelType === null) {
            throw new InvalidConfigException('The "modelType" property must be set.');
        }
    }

    public function autoDetectThumbnail($event)
    {
        foreach ($this->attributes as $attribute => $groupType) {
            if ($groupType !== 'thumbnail') {
                continue;
            }

            if (!$this->owner->canGetProperty($attribute) && !property_exists($this->owner, $attribute)) {
                continue;
            }

            if (empty($this->owner->$attribute)) {
                $publicUrl = rtrim(\Yii::$app->r2->publicUrl ?? '', '/');
                if (!empty($publicUrl) && ($this->owner->canGetProperty('content') || property_exists($this->owner, 'content'))) {
                    $escapedUrl = preg_quote($publicUrl, '/');
                    $pattern = '/' . $escapedUrl . '\/([a-zA-Z0-9_\-]{32}\.(?:png|jpg|jpeg|webp))/i';
                    
                    if (preg_match($pattern, (string)$this->owner->content, $matches)) {
                        $filename = $matches[1];
                        $media = Media::find()->andWhere(['file_name' => $filename])->one();
                        if ($media !== null && ($this->owner->canSetProperty($attribute) || property_exists($this->owner, $attribute))) {
                            $this->owner->$attribute = $media->id;
                        }
                    }
                }
            }
        }
    }

    public function syncMediaLinks($event)
    {
        if (empty($this->owner->id)) {
            return;
        }

        $isInsert = ($event->name === ActiveRecord::EVENT_AFTER_INSERT);

        foreach ($this->attributes as $attribute => $groupType) {
            if (!$this->owner->canGetProperty($attribute) && !property_exists($this->owner, $attribute)) {
                continue;
            }

            $currentValue = $this->owner->$attribute;
            $newVal = $currentValue !== null ? (int)$currentValue : null;

            if ($isInsert) {
                $link = null;
                $oldVal = null;
            } else {
                $link = MediaLink::findOne([
                    'model_type' => $this->modelType,
                    'model_id' => $this->owner->id,
                    'group_type' => $groupType,
                ]);
                $oldVal = $link ? (int)$link->media_id : null;
            }

            if ($oldVal === $newVal && !$isInsert) {
                continue;
            }

            if (empty($newVal)) {
                if ($link !== null) {
                    $link->delete();
                }
            } else {
                if ($link === null) {
                    $link = new MediaLink();
                    $link->model_type = $this->modelType;
                    $link->model_id = $this->owner->id;
                    $link->group_type = $groupType;
                }
                $link->media_id = $newVal;
                $link->save(false);
            }
        }
    }

    public function hasMediaChanges(): bool
    {
        if (empty($this->owner->id)) {
            return false;
        }

        foreach ($this->attributes as $attribute => $groupType) {
            if (!$this->owner->canGetProperty($attribute) && !property_exists($this->owner, $attribute)) {
                continue;
            }
            
            $existingLink = MediaLink::findOne([
                'model_type' => $this->modelType,
                'model_id' => $this->owner->id,
                'group_type' => $groupType,
            ]);
            $oldVal = $existingLink ? (int)$existingLink->media_id : null;
            $newVal = $this->owner->$attribute !== null ? (int)$this->owner->$attribute : null;

            if ($oldVal !== $newVal) {
                return true;
            }
        }
        return false;
    }
}

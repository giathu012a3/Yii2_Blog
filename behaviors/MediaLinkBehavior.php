<?php

declare(strict_types=1);

namespace app\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\MediaLink;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;

class MediaLinkBehavior extends Behavior
{
    public array $attributes = [];
    public ?string $modelType = null;

    public function events(): array
    {
        return [
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

    public function syncMediaLinks()
    {
        if (empty($this->owner->id)) {
            return;
        }

        foreach ($this->attributes as $attribute => $config) {
            $isMultiple = is_array($config) && ($config['multiple'] ?? false);
            $groupType = is_array($config) ? ($config['groupType'] ?? 'default') : $config;

            if ($isMultiple) {
                $this->syncMultipleLinks($attribute, $groupType);
            } else {
                $this->syncSingleLink($attribute, $groupType);
            }
        }
    }

    private function syncSingleLink(string $attribute, string $groupType)
    {
        $currentValue = $this->owner->$attribute;
        $newVal = $currentValue !== null ? (int)$currentValue : null;

        $link = MediaLink::findOne([
            'model_type' => $this->modelType,
            'model_id' => $this->owner->id,
            'group_type' => $groupType,
        ]);
        $oldVal = $link ? (int)$link->media_id : null;

        if ($oldVal === $newVal && !$this->owner->isNewRecord) {
            return;
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

    private function syncMultipleLinks(string $attribute, string $groupType)
    {
        $currentValue = $this->owner->$attribute;

        $existingLinks = MediaLink::find()
            ->andWhere([
                'model_type' => $this->modelType,
                'model_id' => $this->owner->id,
                'group_type' => $groupType,
            ])
            ->all();

        $oldIds = array_map('intval', ArrayHelper::getColumn($existingLinks, 'media_id'));
        $newIds = array_filter(array_map('intval', (array)$currentValue));
        sort($oldIds);
        sort($newIds);

        if ($oldIds === $newIds && !$this->owner->isNewRecord) {
            return;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            MediaLink::deleteAll([
                'model_type' => $this->modelType,
                'model_id' => $this->owner->id,
                'group_type' => $groupType,
            ]);

            if (!empty($newIds)) {
                $rows = [];
                foreach ($newIds as $mediaId) {
                    $rows[] = [
                        'media_id' => $mediaId,
                        'model_type' => $this->modelType,
                        'model_id' => $this->owner->id,
                        'group_type' => $groupType,
                    ];
                }
                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        MediaLink::tableName(),
                        ['media_id', 'model_type', 'model_id', 'group_type'],
                        $rows
                    )
                    ->execute();
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function hasMediaChanges(): bool
    {
        if (empty($this->owner->id)) {
            return false;
        }

        foreach ($this->attributes as $attribute => $config) {
            $isMultiple = is_array($config) && ($config['multiple'] ?? false);
            $groupType = is_array($config) ? ($config['groupType'] ?? 'default') : $config;
            $currentValue = $this->owner->$attribute;

            $existingLinks = MediaLink::find()
                ->andWhere([
                    'model_type' => $this->modelType,
                    'model_id' => $this->owner->id,
                    'group_type' => $groupType,
                ])
                ->all();

            if ($isMultiple) {
                $oldIds = array_map('intval', ArrayHelper::getColumn($existingLinks, 'media_id'));
                $newIds = array_filter(array_map('intval', (array)$currentValue));
                sort($oldIds);
                sort($newIds);
                if ($oldIds !== $newIds) {
                    return true;
                }
            } else {
                $oldVal = $existingLinks ? (int)$existingLinks[0]->media_id : null;
                $newVal = $currentValue !== null ? (int)$currentValue : null;

                if ($oldVal !== $newVal) {
                    return true;
                }
            }
        }
        return false;
    }
}

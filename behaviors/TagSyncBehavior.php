<?php

declare(strict_types=1);

namespace app\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\Tag;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use Yii;

class TagSyncBehavior extends Behavior
{
    public string $tagNamesAttribute = 'tagNames';
    private ?array $_oldTags = null;
    private array $_oldTagIds = [];

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'syncTags',
            ActiveRecord::EVENT_AFTER_UPDATE => 'syncTags',
        ];
    }

    /**
     * Lazily retrieves, populates, and caches the original tags and their IDs.
     */
    public function getOldTags(): array
    {
        if ($this->_oldTags === null) {
            $tags = $this->owner->tags;
            $this->_oldTags = ArrayHelper::getColumn($tags, 'name');
            $this->_oldTagIds = ArrayHelper::getColumn($tags, 'id');
            
            $tagNamesAttr = $this->tagNamesAttribute;
            if ($this->owner->canSetProperty($tagNamesAttr) || property_exists($this->owner, $tagNamesAttr)) {
                $this->owner->$tagNamesAttr = $this->_oldTags;
            }
        }
        return $this->_oldTags;
    }

    public function syncTags($event)
    {
        $tagNamesAttr = $this->tagNamesAttribute;
        if (!$this->owner->canGetProperty($tagNamesAttr) && !property_exists($this->owner, $tagNamesAttr)) {
            return;
        }

        $names = array_filter(array_map('trim', (array)$this->owner->$tagNamesAttr));
        $insert = ($event->name === ActiveRecord::EVENT_AFTER_INSERT);

        if (!$insert) {
            $oldTags = array_map('trim', $this->getOldTags());
            $newTags = $names;
            sort($oldTags);
            sort($newTags);
            if ($oldTags === $newTags) {
                return;
            }
        }

        if (empty($names)) {
            if (!$insert) {
                $this->owner->unlinkAll('tags', true);
            }
            $this->_oldTags = [];
            $this->_oldTagIds = [];
            return;
        }

        $existingTags = Tag::find()->andWhere(['name' => $names])->all();
        $tagsByName = [];
        foreach ($existingTags as $tag) {
            $tagsByName[mb_strtolower($tag->name)] = $tag;
        }

        $newNames = [];
        foreach (array_unique($names) as $name) {
            $lowerName = mb_strtolower($name);
            if (!isset($tagsByName[$lowerName])) {
                $newNames[] = $name;
            }
        }

        if (!empty($newNames)) {
            foreach ($newNames as $name) {
                $tag = new Tag();
                $tag->name = $name;
                if ($tag->save()) {
                    $tagsByName[mb_strtolower($tag->name)] = $tag;
                }
            }
        }

        $tagIds = [];
        foreach (array_unique($names) as $name) {
            $lowerName = mb_strtolower($name);
            if (isset($tagsByName[$lowerName])) {
                $tag = $tagsByName[$lowerName];
                if ((int)$tag->is_deleted === 1) {
                    $tag->is_deleted = 0;
                    $tag->save(false);
                }
                $tagIds[] = $tag->id;
            }
        }

        if ($insert) {
            if (!empty($tagIds)) {
                $rows = [];
                foreach ($tagIds as $tagId) {
                    $rows[] = [$this->owner->id, $tagId];
                }
                Yii::$app->db->createCommand()
                    ->batchInsert('post_tag', ['post_id', 'tag_id'], $rows)
                    ->execute();
            }
        } else {
            $this->getOldTags();
            $oldTagIds = $this->_oldTagIds;
            
            $toLink = array_diff($tagIds, $oldTagIds);
            $toUnlink = array_diff($oldTagIds, $tagIds);

            if (!empty($toUnlink)) {
                Yii::$app->db->createCommand()
                    ->delete('post_tag', ['post_id' => $this->owner->id, 'tag_id' => $toUnlink])
                    ->execute();
            }

            if (!empty($toLink)) {
                $rows = [];
                foreach ($toLink as $tagId) {
                    $rows[] = [$this->owner->id, $tagId];
                }
                Yii::$app->db->createCommand()
                    ->batchInsert('post_tag', ['post_id', 'tag_id'], $rows)
                    ->execute();
            }
        }

        unset($this->owner->tags);
        unset($this->owner->postTags);

        $this->_oldTags = $names;
        $this->_oldTagIds = $tagIds;
    }
}

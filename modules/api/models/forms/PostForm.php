<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Post;
use app\models\Category;
use app\models\Tag;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use Yii;

class PostForm extends Post
{
    public $tagNames = [];
    private $_oldTagNames = [];

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id'], 'filter' => ['is_deleted' => 0], 'message' => 'The selected category is invalid or deleted.'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['tagNames'], 'safe'],
            [['title'], 'validateHasChanges', 'skipOnEmpty' => false],
        ]);
    }

    public function load($data, $formName = null): bool
    {
        if (is_array($data)) {
            unset($data['author_id'], $data['view_count'], $data['is_deleted'], $data['deleted_at'], $data['published_at']);
        }
        return parent::load($data, $formName);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->tagNames = ArrayHelper::getColumn($this->tags, 'name');
        $this->_oldTagNames = $this->tagNames;
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at'], $dirty['published_at'], $dirty['slug'], $dirty['created_at']);

        $oldTags = array_map('trim', $this->_oldTagNames);
        $newTags = array_filter(array_map('trim', (array)$this->tagNames));

        sort($oldTags);
        sort($newTags);

        if (empty($dirty) && $oldTags === $newTags) {
            $this->addError($attribute, 'No changes detected.');
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->syncTags($insert);
    }

    protected function syncTags(bool $insert = false)
    {
        if (!$insert) {
            $this->unlinkAll('tags', true);
        }

        $names = array_filter(array_map('trim', (array)$this->tagNames));
        if (empty($names)) {
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
            $time = time();
            $tagRows = [];
            foreach ($newNames as $name) {
                $tagRows[] = [
                    'name' => $name,
                    'slug' => Inflector::slug($name),
                    'created_at' => $time,
                    'updated_at' => $time,
                    'is_deleted' => 0,
                ];
            }

            Yii::$app->db->createCommand()
                ->batchInsert(Tag::tableName(), ['name', 'slug', 'created_at', 'updated_at', 'is_deleted'], $tagRows)
                ->execute();

            $newTags = Tag::find()->andWhere(['name' => $newNames])->all();
            foreach ($newTags as $tag) {
                $tagsByName[mb_strtolower($tag->name)] = $tag;
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

        if (!empty($tagIds)) {
            $rows = [];
            foreach ($tagIds as $tagId) {
                $rows[] = [$this->id, $tagId];
            }
            Yii::$app->db->createCommand()
                ->batchInsert('post_tag', ['post_id', 'tag_id'], $rows)
                ->execute();
        }
    }
}

<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Post;
use app\models\Category;
use app\models\Media;
use app\models\MediaLink;
use app\models\Tag;
use yii\helpers\ArrayHelper;
use Yii;

class PostForm extends Post
{
    public $tagNames = [];
    public $thumbnail_id;

    private array $_oldTags = [];
    private array $_oldTagIds = [];
    private ?int $_oldThumbnailId = null;

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['title', 'content', 'category_id', 'status', 'tagNames', 'thumbnail_id'],
        ];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [
                ['category_id'],
                'exist',
                'targetClass' => Category::class,
                'targetAttribute' => 'id',
                'filter' => ['is_deleted' => 0],
                'message' => \Yii::t('app', 'The selected category is invalid or deleted.')
            ],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['thumbnail_id'], 'default', 'value' => null],
            [['thumbnail_id'], 'integer'],
            [
                ['thumbnail_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Media::class,
                'targetAttribute' => 'id',
                'filter' => function ($query) {
                    if (Yii::$app->user->can('updatePost')) {
                        return;
                    }
                    $userId = Yii::$app->user->id ?? 0;
                    $query->andWhere(['user_id' => $userId]);
                },
                'message' => \Yii::t('app', 'The selected thumbnail is invalid.')
            ],
            [['tagNames'], 'each', 'rule' => ['string', 'max' => 255], 'skipOnEmpty' => true],
            [['title'], 'validateHasChanges', 'skipOnEmpty' => false],
        ]);
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at'], $dirty['published_at'], $dirty['slug'], $dirty['created_at']);

        $oldTags = $this->_oldTags;
        $newTags = array_filter(array_map('trim', (array)$this->tagNames));
        sort($oldTags);
        sort($newTags);

        $oldThumb = $this->_oldThumbnailId !== null ? (int)$this->_oldThumbnailId : null;
        $newThumb = $this->thumbnail_id !== null && $this->thumbnail_id !== '' ? (int)$this->thumbnail_id : null;

        if (empty($dirty) && $oldTags === $newTags && $oldThumb === $newThumb) {
            $this->addError($attribute, \Yii::t('app', 'No changes detected.'));
        }
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->thumbnail_id = $this->thumbnail ? $this->thumbnail->id : null;
        $this->tagNames = ArrayHelper::getColumn($this->tags, 'name');

        $this->_oldThumbnailId = $this->thumbnail_id;
        $this->_oldTags = $this->tagNames;
        $this->_oldTagIds = ArrayHelper::getColumn($this->tags, 'id');
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->thumbnail_id)) {
            $this->thumbnail_id = $this->detectThumbnailId();
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->syncThumbnailLink();
        $this->syncTags();
    }

    private function syncThumbnailLink(): void
    {
        $newVal = $this->thumbnail_id !== null ? $this->thumbnail_id : null;
        $oldVal = $this->_oldThumbnailId;

        if ($oldVal === $newVal) {
            return;
        }

        MediaLink::deleteAll([
            'model_type' => 'Post',
            'model_id' => $this->id,
            'group_type' => 'thumbnail',
        ]);

        if (!empty($newVal)) {
            $link = new MediaLink([
                'model_type' => 'Post',
                'model_id' => $this->id,
                'group_type' => 'thumbnail',
                'media_id' => $newVal,
            ]);
            $link->save(false);
        }

        $this->_oldThumbnailId = $newVal;
    }

    private function syncTags(): void
    {
        $uniqueNames = $this->normalizeTagNames((array)$this->tagNames);
        $names = array_values($uniqueNames);

        $oldTags = $this->_oldTags;
        $newTags = $names;
        sort($oldTags);
        sort($newTags);
        if ($oldTags === $newTags) {
            return;
        }

        if (empty($uniqueNames)) {
            Yii::$app->db->createCommand()
                ->delete('post_tag', ['post_id' => $this->id])
                ->execute();
            $this->_oldTags = [];
            $this->_oldTagIds = [];
            unset($this->tags);
            unset($this->postTags);
            return;
        }

        $tagIds = $this->getOrCreateTagIds($uniqueNames);

        $oldTagIds = $this->_oldTagIds;
        $toLink = array_diff($tagIds, $oldTagIds);
        $toUnlink = array_diff($oldTagIds, $tagIds);

        if (!empty($toUnlink)) {
            Yii::$app->db->createCommand()
                ->delete('post_tag', ['post_id' => $this->id, 'tag_id' => $toUnlink])
                ->execute();
        }

        if (!empty($toLink)) {
            $rows = [];
            foreach ($toLink as $tagId) {
                $rows[] = [$this->id, $tagId];
            }
            Yii::$app->db->createCommand()
                ->batchInsert('post_tag', ['post_id', 'tag_id'], $rows)
                ->execute();
        }

        $this->_oldTags = $names;
        $this->_oldTagIds = $tagIds;

        unset($this->tags);
        unset($this->postTags);
    }

    private function normalizeTagNames(array $names): array
    {
        $unique = [];
        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $lower = mb_strtolower($name);
            if (!isset($unique[$lower])) {
                $unique[$lower] = $name;
            }
        }

        return $unique;
    }

    private function getOrCreateTagIds(array $uniqueNames): array
    {
        $existingTags = Tag::find()->andWhere(['name' => array_values($uniqueNames)])->all();
        $tagsByName = [];
        foreach ($existingTags as $tag) {
            $tagsByName[mb_strtolower($tag->name)] = $tag;
        }

        $tagIds = [];
        foreach ($uniqueNames as $lower => $name) {
            if (isset($tagsByName[$lower])) {
                $tag = $tagsByName[$lower];
                if ((int)$tag->is_deleted === 1) {
                    $tag->is_deleted = 0;
                    $tag->save(false);
                }
            } else {
                $tag = new Tag();
                $tag->name = $name;
                $tag->save();
            }

            if ($tag->id) {
                $tagIds[] = (int)$tag->id;
            }
        }

        return $tagIds;
    }

    private function detectThumbnailId(): ?int
    {
        $publicUrl = rtrim(Yii::$app->r2->publicUrl ?? '', '/');

        if (empty($publicUrl) || empty($this->content)) {
            return null;
        }

        $escapedUrl = preg_quote($publicUrl, '/');
        $pattern = '/' . $escapedUrl . '\/([a-zA-Z0-9_\-]{32}\.(?:png|jpg|jpeg|webp))/i';

        if (!preg_match($pattern, (string)$this->content, $matches)) {
            return null;
        }

        $media = Media::find()
            ->select('id')
            ->where(['file_name' => $matches[1]])
            ->one();

        return $media ? (int)$media->id : null;
    }
}

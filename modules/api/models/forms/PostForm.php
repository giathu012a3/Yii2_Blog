<?php

namespace app\modules\api\models\forms;

use app\models\Category;
use app\models\Media;
use app\models\Post;
use app\models\Tag;
use Yii;
use yii\web\UploadedFile;

class PostForm extends Post
{
    public $tag_list = null;
    public $warnings;
    public $thumbnail_file;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_DEFAULT] = ['category_id', 'title', 'description', 'thumbnail_file', 'content', 'status', 'tag_list'];
        return $scenarios;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['content'], 'required'],
            [['tag_list'], 'safe'],
            [['thumbnail_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp', 'maxSize' => 5 * 1024 * 1024],
        ]);
    }

    public function fields()
    {
        $fields = parent::fields();
        if (!empty($this->warnings)) {
            $fields['warnings'] = 'warnings';
        }
        return $fields;
    }


    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->author_id = Yii::$app->user->id;
        }
        $file = UploadedFile::getInstanceByName('thumbnail_file');
        if ($file) {
            $this->thumbnail_file = $file;
        }
        return parent::beforeValidate();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->tag_list !== null) {
            if (is_array($this->tag_list)) {
                $tagIds = $this->resolveTagIds($this->tag_list);
                $this->syncTags($tagIds);
            } else {
                Yii::warning("Invalid tag_list format for post ID {$this->id}: expected array, got " . gettype($this->tag_list));
                $this->warnings[] = 'tag_list must be an array of strings, tags were not synced.';
            }
        }

        $this->syncThumbnail();
        $this->syncContentImages();
    }

    protected function syncContentImages()
    {
        $mediaIds = Media::extractAllImagesInContentById($this->content);

        if (!empty($mediaIds)) {
            Media::updateAll(
                [
                    'model_id' => $this->id,
                    'model_name' => self::tableName(),
                ],
                [
                    'id' => $mediaIds,
                    'model_id' => null
                ]
            );
        }

        $removedMedia = Media::find()
            ->where([
                'model_id' => $this->id,
                'model_name' => self::tableName(),
                'collection' => 'content'
            ])
            ->andWhere(['not', ['id' => $mediaIds]])
            ->all();

        foreach ($removedMedia as $media) {
            $media->deleteMedia(true);
        }
    }

    protected function resolveTagIds(array $inputs): array
    {
        $names = array_filter(array_unique(
            array_map(fn($i) => mb_strtolower(trim((string)$i), 'UTF-8'), $inputs)
        ));

        if (empty($names)) {
            return [];
        }

        $existingTags = Tag::find()->where(['name' => $names])->indexBy('name')->all();

        $tagIds = [];
        foreach ($names as $name) {
            if (isset($existingTags[$name])) {
                $tagIds[] = (int)$existingTags[$name]->id;
            } else {
                $tagIds[] = $this->createTag($name);
            }
        }

        return array_filter($tagIds);
    }

    protected function createTag(string $name): ?int
    {
        $tag = new Tag();
        $tag->name = $name;

        if (!$tag->validate()) {
            $errors = implode(', ', $tag->getErrorSummary(true));
            $this->warnings[] = "Validation failed for tag '{$name}': {$errors}";
            return null;
        }

        try {
            if ($tag->save(false)) {
                return (int)$tag->id;
            }
        } catch (\yii\db\Exception $e) {

            if (isset($e->errorInfo[0]) && $e->errorInfo[0] === '23000') {
                $existing = Tag::findOne(['name' => $name]);
                if ($existing) {
                    return (int)$existing->id;
                }
            }
            Yii::error("Database error creating tag '{$name}': " . $e->getMessage());
            $this->warnings[] = "Database error for tag '{$name}': " . $e->getMessage();
            return null;
        }

        $this->warnings[] = "Failed to save tag '{$name}'.";
        return null;
    }


    protected function syncTags(array $tagIds)
    {
        $currentTagIds = (new \yii\db\Query())
            ->select('tag_id')
            ->from('post_tag')
            ->where(['post_id' => $this->id])
            ->column();

        $currentTagIds = array_map('intval', $currentTagIds);

        $tagsToAdd = array_diff($tagIds, $currentTagIds);
        $tagsToRemove = array_diff($currentTagIds, $tagIds);

        if (!empty($tagsToRemove)) {
            Yii::$app->db->createCommand()
                ->delete('post_tag', [
                    'post_id' => $this->id,
                    'tag_id' => $tagsToRemove,
                ])
                ->execute();
        }

        if (!empty($tagsToAdd)) {
            $rows = [];
            $now = time();
            foreach ($tagsToAdd as $tagId) {
                $rows[] = [$this->id, $tagId, $now];
            }
            Yii::$app->db->createCommand()
                ->batchInsert('post_tag', ['post_id', 'tag_id', 'created_at'], $rows)
                ->execute();
        }
    }

    protected function syncThumbnail()
    {

        if ($this->thumbnail_file === '') {
            $this->deleteOldThumbnail(true);
            return;
        }

        if ($this->thumbnail_file) {
            $this->handleUploadedThumbnail();
            return;
        }

        $this->handleFallbackThumbnail();
    }

    private function getOldThumbnail()
    {
        return Media::findOne([
            'model_id' => $this->id,
            'model_name' => self::tableName(),
            'collection' => 'thumbnail'
        ]);
    }

    private function deleteOldThumbnail(bool $deleteFromR2 = true)
    {
        $oldThumbnail = $this->getOldThumbnail();
        if ($oldThumbnail) {
            $oldThumbnail->deleteMedia($deleteFromR2);
        }
    }

    private function handleUploadedThumbnail()
    {
        $this->deleteOldThumbnail(true);
        $media = Media::uploadAndCreate($this->thumbnail_file, 'thumbnail', $this->id, self::tableName());
        if (!$media) {
            $this->warnings[] = "Fail to upload thumbnail.";
        }
    }

    private function handleFallbackThumbnail()
    {
        $thumbnailSource = Media::findFirstImageInContent($this->content);
        if (empty($thumbnailSource)) {
            return;
        }
        $existingMedia = Media::findByIdOrUrl($thumbnailSource);


        $url = $existingMedia ? $existingMedia->url : $thumbnailSource;
        $oldThumbnail = $this->getOldThumbnail();
        if ($oldThumbnail && $oldThumbnail->url === $url) {
            return;
        }

        $this->deleteOldThumbnail(false);
        $media = new Media([
            'model_id' => $this->id,
            'model_name' => self::tableName(),
            'collection' => 'thumbnail',
            'url' => $url,
            'storage_key' => $existingMedia ? $existingMedia->storage_key : null,
            'file_size' => $existingMedia ? $existingMedia->file_size : null,
            'mime_type' => $existingMedia ? $existingMedia->mime_type : null,
        ]);
        if (!$media->save()) {
            $this->warnings[] = "Fail to create thumbnail.";
        }
    }
}

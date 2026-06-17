<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Post;
use app\models\Category;
use app\models\Media;
use Yii;

class PostForm extends Post
{
    public $tagNames = [];
    public $thumbnail_id;

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
                'message' => 'The selected category is invalid or deleted.'
            ],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['thumbnail_id'], 'integer'],
            [
                ['thumbnail_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Media::class,
                'targetAttribute' => 'id',
                'filter' => ['user_id' => Yii::$app->user->id],
                'message' => 'The selected thumbnail is invalid.'
            ],
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

        $oldTags = array_map('trim', $this->getOldTags());
        $newTags = array_filter(array_map('trim', (array)$this->tagNames));
        sort($oldTags);
        sort($newTags);

        if (empty($dirty) && $oldTags === $newTags && !$this->hasMediaChanges()) {
            $this->addError($attribute, 'No changes detected.');
        }
    }
}

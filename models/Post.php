<?php

namespace app\models;

use app\behaviors\SlugBehavior;
use app\behaviors\SoftDeleteBehaviors;
use app\models\base\BasePost;
use yii\behaviors\TimestampBehavior;

class Post extends BasePost
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const NOT_DELETED = 0;
    const DELETED = 1;


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'slug' => [
                'class' => SlugBehavior::class,
                'attribute' => 'title'
            ],
            SoftDeleteBehaviors::class,
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->status == self::STATUS_PUBLISHED) {
                $oldPubishedAt = $this->getOldAttribute('published_at');
                if (empty($oldPubishedAt) && empty($this->published_at)) {
                    $this->published_at = time();
                }
            }
            return true;
        }
        return false;
    }

    public function fields()
    {
        return [
            'id',
            'category_id',
            'author_id',
            'title',
            'description',
            'content',
            'status',
            'thumbnail',
            'slug',
            'view_count',
            'published_at',
            'created_at',
        ];
    }

    public function extraFields()
    {
        return [
            'category',
            'author',
            'tags',
            'comments'
        ];
    }


    /**
     * {@inheritdoc}
     * @return \app\models\query\PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PostQuery(get_called_class());
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->viaTable('post_tag', ['post_id' => 'id']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::class, ['post_id' => 'id'])
            ->andWhere([
                'status' => Comment::STATUS_ACTIVE,
                'parent_id' => null
            ]);
    }
}

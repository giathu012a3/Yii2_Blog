<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\PostBase;
use app\models\query\PostQuery;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Post model extending PostBase.
 */
class Post extends PostBase
{
    public const STATUS_PUBLISHED = 1;
    public const STATUS_DRAFT = 0;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'title',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): PostQuery
    {
        return new PostQuery(static::class);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[PostLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostLikes()
    {
        return $this->hasMany(PostLike::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[LikedUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLikedUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->via('postLikes');
    }

    /**
     * Gets query for [[PostTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostTags()
    {
        return $this->hasMany(PostTag::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Tags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('postTags');
    }

    /**
     * Gets query for [[MediaLinks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMediaLinks()
    {
        return $this->hasMany(MediaLink::class, ['model_id' => 'id'])
            ->onCondition(['model_type' => 'Post']);
    }

    /**
     * Gets query for [[Media]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedia()
    {
        return $this->hasMany(Media::class, ['id' => 'media_id'])->via('mediaLinks');
    }
}

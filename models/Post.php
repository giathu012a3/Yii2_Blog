<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\PostBase;
use app\models\query\PostQuery;
use app\behaviors\SoftDeleteBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use app\models\MediaLink;
use app\models\Media;
use app\models\Tag;

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
                'ensureUnique' => true,
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'author_id',
                'updatedByAttribute' => false,
            ],
            [
                'class' => SoftDeleteBehavior::class,
            ],
            [
                'class' => \app\behaviors\MediaLinkBehavior::class,
                'modelType' => 'Post',
                'attributes' => [
                    'thumbnail_id' => 'thumbnail',
                ],
            ],
            [
                'class' => \app\behaviors\TagSyncBehavior::class,
                'tagNamesAttribute' => 'tagNames',
            ],
            [
                'class' => \app\behaviors\ContentMediaSyncBehavior::class,
                'contentAttribute' => 'content',
                'modelType' => 'Post',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['title'], 'unique', 'message' => 'This title has already been taken.'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->status === self::STATUS_PUBLISHED && $this->published_at === null) {
            $this->published_at = time();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return [
            'id',
            'title',
            'slug',
            'status',
            'view_count',
            'category_id',
            'author_id',
            'published_at',
            'created_at',
            'updated_at'
        ];
    }

    public function extraFields(): array
    {
        return ['content', 'category', 'tags', 'author', 'thumbnail'];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): PostQuery
    {
        return new PostQuery(static::class);
    }

    public static function findPublishedBySlug(string $slug): ?self
    {
        return static::find()
            ->active()
            ->andWhere(['status' => self::STATUS_PUBLISHED, 'slug' => $slug])
            ->one();
    }

    public static function findPublishedById($id): ?self
    {
        return static::find()
            ->active()
            ->andWhere(['status' => self::STATUS_PUBLISHED, 'id' => $id])
            ->one();
    }

    public static function findActive($id): ?self
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }

    public function incrementViewCount(): void
    {
        $this->updateCounters(['view_count' => 1]);
    }

    public function toggleLike(int $userId): array
    {
        $like = PostLike::findOne(['post_id' => $this->id, 'user_id' => $userId]);
        if ($like !== null) {
            $like->delete();
            return [
                'liked' => false,
                'message' => 'Post unliked successfully.',
            ];
        }

        $like = new PostLike();
        $like->post_id = $this->id;
        $like->user_id = $userId;
        if ($like->save()) {
            return [
                'liked' => true,
                'message' => 'Post liked successfully.',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Failed to like post.');
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

    public function getMedia()
    {
        return $this->hasMany(Media::class, ['id' => 'media_id'])->via('mediaLinks');
    }

    public function getThumbnailLink()
    {
        return $this->hasOne(MediaLink::class, ['model_id' => 'id'])
            ->onCondition(['model_type' => 'Post', 'group_type' => 'thumbnail']);
    }

    public function getThumbnail()
    {
        return $this->hasOne(Media::class, ['id' => 'media_id'])->via('thumbnailLink');
    }
}

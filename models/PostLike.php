<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\PostLikeBase;
use app\models\query\PostLikeQuery;
use yii\behaviors\TimestampBehavior;

/**
 * PostLike model extending PostLikeBase.
 */
class PostLike extends PostLikeBase
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): PostLikeQuery
    {
        return new PostLikeQuery(static::class);
    }

    /**
     * Gets query for [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}

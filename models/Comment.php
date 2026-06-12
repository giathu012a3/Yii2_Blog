<?php

declare(strict_types=1);

namespace app\models;

use app\behaviors\SoftDeleteBehavior;
use app\models\base\CommentBase;
use app\models\query\CommentQuery;
use yii\behaviors\TimestampBehavior;

/**
 * Comment model extending CommentBase.
 */
class Comment extends CommentBase
{
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
                'class' => SoftDeleteBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): CommentQuery
    {
        return new CommentQuery(static::class);
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

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Comment::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Replies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id']);
    }

    public function fields(): array
    {
        return ['id', 'post_id', 'parent_id', 'content', 'created_at', 'updated_at'];
    }

    public function extraFields(): array
    {
        return [
            'user'    => function () {
                return $this->user
                    ? ['id' => $this->user->id, 'username' => $this->user->username]
                    : null;
            },
            'replies' => function () {
                if ($this->parent_id !== null) {
                    return null;
                }
                return array_map(function (Comment $reply) {
                    return [
                        'id'         => $reply->id,
                        'post_id'    => $reply->post_id,
                        'parent_id'  => $reply->parent_id,
                        'content'    => $reply->content,
                        'created_at' => $reply->created_at,
                        'updated_at' => $reply->updated_at,
                        'user'       => $reply->user
                            ? ['id' => $reply->user->id, 'username' => $reply->user->username]
                            : null,
                    ];
                }, $this->isRelationPopulated('replies') ? $this->replies : []);
            },
        ];
    }
}

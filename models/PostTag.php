<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\PostTagBase;
use app\models\query\PostTagQuery;

/**
 * PostTag model extending PostTagBase.
 */
class PostTag extends PostTagBase
{
    /**
     * {@inheritdoc}
     */
    public static function find(): PostTagQuery
    {
        return new PostTagQuery(static::class);
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
     * Gets query for [[Tag]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }
}

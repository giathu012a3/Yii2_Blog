<?php

namespace app\models\query;

use app\models\Post;

/**
 * This is the ActiveQuery class for [[\app\models\base\BasePost]].
 *
 * @see \app\models\Post
 */
class PostQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Post[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Post|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function notDelete()
    {
        return $this->andWhere(['post.is_deleted' => Post::NOT_DELETED]);
    }

    public function deleted()
    {
        return $this->andWhere(['post.is_deleted' => Post::DELETED]);
    }

    public function withDeleted()
    {
        return $this;
    }

    public function published()
    {
        return $this->andWhere(['post.status' => Post::STATUS_PUBLISHED]);
    }

    public function publishedOrOwn($userId)
    {
        return $this->andWhere([
            'or',
            ['post.status' => Post::STATUS_PUBLISHED],
            ['post.author_id' => $userId]
        ]);
    }
}

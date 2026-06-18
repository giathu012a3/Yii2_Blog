<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\CommentBase]].
 *
 * @see \app\models\base\CommentBase
 */
class CommentQuery extends \yii\db\ActiveQuery
{
    public function active(): static
    {
        return $this->andWhere(['is_deleted' => 0]);
    }

    public function threadedByPost(int $postId)
    {
        return $this
            ->where(['post_id' => $postId, 'parent_id' => null, 'is_deleted' => 0])
            ->with([
                'replies' => function ($q) {
                    $q->andWhere(['is_deleted' => 0])
                      ->with('user')
                      ->orderBy(['created_at' => SORT_ASC]);
                },
                'user',
            ])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    public function byId(int $id): static
    {
        return $this->andWhere(['comment.id' => $id]);
    }

    public function all($db = null)
    {
        return parent::all($db);
    }

    public function one($db = null)
    {
        return parent::one($db);
    }
}

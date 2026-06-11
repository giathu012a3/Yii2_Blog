<?php

declare(strict_types=1);

namespace app\models\query;


/**
 * ActiveQuery for Post model.
 *
 * @see \app\models\Post
 */
class PostQuery extends \yii\db\ActiveQuery
{
    /**
     * Filter out soft-deleted posts.
     */
    public function active(): static
    {
        return $this->andWhere(['post.is_deleted' => 0]);
    }

    /**
     * Apply visibility scope based on the identity and role.
     *
     * - Guest          : only Published posts
     * - Author (logged) : Published posts + own Drafts
     * - Admin           : all posts (no status filter)
     */
    public function visibleTo(?int $userId, bool $isAdmin = false): static
    {
        if ($isAdmin) {
            return $this;
        }

        if ($userId === null) {
            return $this->andWhere(['post.status' => 1]);
        }

        return $this->andWhere([
            'or',
            ['post.status' => 1],
            ['post.author_id' => $userId],
        ]);
    }

}

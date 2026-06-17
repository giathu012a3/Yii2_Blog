<?php

declare(strict_types=1);

namespace app\models\query;

use app\models\Post;

/**
 * ActiveQuery for Post model.
 *
 * @see \app\models\Post
 */
class PostQuery extends \yii\db\ActiveQuery
{
    public function active(): static
    {
        return $this->andWhere(['post.is_deleted' => 0]);
    }

    public function published(): static
    {
        return $this->andWhere(['post.status' => Post::STATUS_PUBLISHED]);
    }
}

<?php

declare(strict_types=1);

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * UserTokenQuery là Active Query cho model UserToken.
 */
class UserTokenQuery extends ActiveQuery
{
    /**
     * Lọc các token còn hiệu lực (chưa hết hạn).
     * Token có expired_at = NULL được xem là không hết hạn.
     */
    public function active(): static
    {
        return $this->andWhere([
            'OR',
            ['expired_at' => null],
            ['>', 'expired_at', time()],
        ]);
    }

    /**
     * Lọc theo user_id.
     */
    public function byUser(int $userId): static
    {
        return $this->andWhere(['user_id' => $userId]);
    }
}

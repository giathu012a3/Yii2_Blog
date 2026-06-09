<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\UserBase]].
 *
 * @see \app\models\base\UserBase
 */
class UserQuery extends \yii\db\ActiveQuery
{
    /**
     * Filter only active and non-deleted users.
     */
    public function active()
    {
        return $this->andWhere(['is_deleted' => 0, 'status' => 1]);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\UserBase[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\UserBase|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

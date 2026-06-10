<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\CategoryBase]].
 *
 * @see \app\models\base\CategoryBase
 */
class CategoryQuery extends \yii\db\ActiveQuery
{
    /**
     * Filter only active (non-deleted) categories.
     */
    public function active()
    {
        return $this->andWhere(['is_deleted' => 0]);
    }


    /**
     * {@inheritdoc}
     * @return \app\models\base\CategoryBase[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\CategoryBase|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

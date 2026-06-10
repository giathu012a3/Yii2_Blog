<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\TagBase]].
 *
 * @see \app\models\base\TagBase
 */
class TagQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['is_deleted' => 0]);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\TagBase[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\TagBase|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

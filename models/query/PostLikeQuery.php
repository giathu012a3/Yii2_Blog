<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\PostLikeBase]].
 *
 * @see \app\models\base\PostLikeBase
 */
class PostLikeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\base\PostLikeBase[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\PostLikeBase|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

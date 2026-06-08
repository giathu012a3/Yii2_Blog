<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\PostTagBase]].
 *
 * @see \app\models\base\PostTagBase
 */
class PostTagQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\base\PostTagBase[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\PostTagBase|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

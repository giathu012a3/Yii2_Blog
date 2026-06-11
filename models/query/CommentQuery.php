<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\BaseComment]].
 *
 * @see \app\models\base\BaseComment
 */
class CommentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\base\BaseComment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\BaseComment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\base\MediaLinkBase]].
 *
 * @see \app\models\base\MediaLinkBase
 */
class MediaLinkQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\base\MediaLinkBase[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\base\MediaLinkBase|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

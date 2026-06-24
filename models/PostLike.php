<?php

namespace app\models;

use app\models\base\BasePostLike;
use yii\behaviors\TimestampBehavior;

class PostLike extends BasePostLike
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ]
        ];
    }


}

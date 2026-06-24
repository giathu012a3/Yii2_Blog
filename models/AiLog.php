<?php
namespace app\models;

use app\models\base\BaseAiLog;
use yii\behaviors\TimestampBehavior;

class AiLog extends BaseAiLog
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 0;

    public function behaviors()
    {
       return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ]
       ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}


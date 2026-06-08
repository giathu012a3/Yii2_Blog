<?php
namespace app\models;

use app\models\base\BaseUserAccessToken;
use yii\behaviors\TimestampBehavior;

class UserAccessToken extends BaseUserAccessToken
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class,['id' => 'user_id']);
    }
}

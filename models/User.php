<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\BaseUser;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

class User extends BaseUser implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['status'], 'in', 'range', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]]
        ]);
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $accessToken = UserAccessToken::find()
            ->where(['token' => $token])
            ->andWhere(['or', ['>', 'expires_at', time()], ['expires_at' => null]])
            ->andWhere(['or', ['revoked_at' => null]])
            ->one();
        if ($accessToken) {
            return static::findOne(['id' => $accessToken->user_id, 'status' => self::STATUS_ACTIVE]);
        }
        return null;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public static function findByUsername(string $username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    public function setPassword(string $password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    public function validatePassword(string $password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}

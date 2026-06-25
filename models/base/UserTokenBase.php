<?php

declare(strict_types=1);

namespace app\models\base;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int|null $expired_at  Unix timestamp hết hạn. NULL = không hết hạn.
 * @property int $created_at
 *
 * @property \app\models\User $user
 */
class UserTokenBase extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id', 'expired_at', 'created_at'], 'integer'],
            [['token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => \app\models\User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'user_id'    => 'User ID',
            'token'      => 'Token',
            'expired_at' => 'Expired At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Quan hệ đến User.
     */
    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\UserTokenQuery
     */
    public static function find(): \app\models\query\UserTokenQuery
    {
        return new \app\models\query\UserTokenQuery(get_called_class());
    }
}

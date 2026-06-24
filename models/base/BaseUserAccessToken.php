<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "user_access_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int|null $expires_at
 * @property int|null $revoked_at
 * @property string|null $device_name
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class BaseUserAccessToken extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expires_at', 'revoked_at', 'device_name', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'token'], 'required'],
            [['user_id', 'expires_at', 'revoked_at', 'created_at', 'updated_at'], 'integer'],
            [['token', 'device_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'expires_at' => 'Expires At',
            'revoked_at' => 'Revoked At',
            'device_name' => 'Device Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}

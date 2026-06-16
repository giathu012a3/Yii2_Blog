<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "media".
 *
 * @property int $id
 * @property int $user_id
 * @property string $file_name
 * @property string $file_url
 * @property string $mime_type
 * @property int $size
 * @property int|null $created_at
 */
class MediaBase extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'default', 'value' => null],
            [['user_id', 'mime_type', 'size'], 'required'],
            [['user_id', 'size', 'created_at'], 'integer'],
            [['file_name', 'file_url'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 50],
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
            'file_name' => 'File Name',
            'file_url' => 'File Url',
            'mime_type' => 'Mime Type',
            'size' => 'Size',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\MediaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\MediaQuery(get_called_class());
    }

}

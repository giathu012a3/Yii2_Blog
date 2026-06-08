<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "media_link".
 *
 * @property int $id
 * @property int $media_id
 * @property string $model_type
 * @property int $model_id
 * @property string $group_type
 */
class MediaLinkBase extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'media_link';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_type'], 'default', 'value' => 'default'],
            [['media_id', 'model_type', 'model_id'], 'required'],
            [['media_id', 'model_id'], 'integer'],
            [['model_type'], 'string', 'max' => 100],
            [['group_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'media_id' => 'Media ID',
            'model_type' => 'Model Type',
            'model_id' => 'Model ID',
            'group_type' => 'Group Type',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\MediaLinkQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\MediaLinkQuery(get_called_class());
    }

}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "media".
 *
 * @property int $id
 * @property int $model_id
 * @property string $model_name
 * @property string|null $url
 * @property int|null $file_size
 * @property string|null $mime_type
 * @property int|null $created_at
 */
class BaseMedia extends \yii\db\ActiveRecord
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
            [['url', 'file_size', 'mime_type', 'created_at'], 'default', 'value' => null],
            [['model_id', 'model_name'], 'safe'],
            [['model_id', 'file_size', 'created_at'], 'integer'],
            [['model_name', 'url', 'mime_type','collection'], 'string', 'max' => 255],
            [['collection'], 'default', 'value' => 'content'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_id' => 'Model ID',
            'model_name' => 'Model Name',
            'url' => 'Url',
            'file_size' => 'File Size',
            'mime_type' => 'Mime Type',
            'created_at' => 'Created At',
        ];
    }

}

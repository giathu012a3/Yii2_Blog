<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property string $slug
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class BaseCategory extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['name', 'slug'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'slug' => 'Slug',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}

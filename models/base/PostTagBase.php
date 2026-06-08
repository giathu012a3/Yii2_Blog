<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "post_tag".
 *
 * @property int $post_id
 * @property int $tag_id
 */
class PostTagBase extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id', 'tag_id'], 'required'],
            [['post_id', 'tag_id'], 'integer'],
            [['post_id', 'tag_id'], 'unique', 'targetAttribute' => ['post_id', 'tag_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'post_id' => 'Post ID',
            'tag_id' => 'Tag ID',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\PostTagQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PostTagQuery(get_called_class());
    }

}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "post_like".
 *
 * @property int $post_id
 * @property int $user_id
 * @property int|null $created_at
 */
class PostLikeBase extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post_like';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'default', 'value' => null],
            [['post_id', 'user_id'], 'required'],
            [['post_id', 'user_id', 'created_at'], 'integer'],
            [['post_id', 'user_id'], 'unique', 'targetAttribute' => ['post_id', 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'post_id' => 'Post ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\PostLikeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PostLikeQuery(get_called_class());
    }

}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "post_like".
 *
 * @property int $post_id
 * @property int $author_id
 * @property int|null $created_at
 */
class BasePostLike extends \yii\db\ActiveRecord
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
            [['post_id', 'author_id'], 'required'],
            [['post_id', 'author_id', 'created_at'], 'integer'],
            [['post_id', 'author_id'], 'unique', 'targetAttribute' => ['post_id', 'author_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'post_id' => 'Post ID',
            'author_id' => 'Author ID',
            'created_at' => 'Created At',
        ];
    }

}

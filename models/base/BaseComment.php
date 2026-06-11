<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $post_id
 * @property int $author_id
 * @property int|null $parent_id
 * @property string|null $content
 * @property int $status
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class BaseComment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'content', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['post_id', 'author_id'], 'required'],
            [['post_id', 'author_id', 'parent_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'author_id' => 'Author ID',
            'parent_id' => 'Parent ID',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}

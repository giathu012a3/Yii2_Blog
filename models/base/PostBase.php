<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string|null $slug
 * @property int $status
 * @property int $view_count
 * @property int $category_id
 * @property int $author_id
 * @property int|null $published_at
 * @property int $is_deleted
 * @property int|null $deleted_at
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class PostBase extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['slug', 'published_at', 'deleted_at', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 0],
            [['is_deleted'], 'default', 'value' => 0],
            [['title', 'content', 'category_id'], 'required'],
            [['content'], 'string'],
            [['status', 'view_count', 'category_id', 'author_id', 'published_at', 'is_deleted', 'deleted_at', 'created_at', 'updated_at'], 'integer'],
            [['title', 'slug'], 'string', 'max' => 255],
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
            'title' => 'Title',
            'content' => 'Content',
            'slug' => 'Slug',
            'status' => 'Status',
            'view_count' => 'View Count',
            'category_id' => 'Category ID',
            'author_id' => 'Author ID',
            'published_at' => 'Published At',
            'is_deleted' => 'Is Deleted',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\PostQuery(get_called_class());
    }

}

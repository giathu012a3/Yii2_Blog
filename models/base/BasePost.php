<?php

namespace app\models\base;


/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property int $category_id
 * @property int $author_id
 * @property string $title
 * @property string|null $description
 * @property string $slug
 * @property string|null $content
 * @property int $status
 * @property int|null $published_at
 * @property int $view_count
 * @property int $is_deleted
 * @property int|null $deleted_at
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class BasePost extends \yii\db\ActiveRecord
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
            [['description', 'content', 'published_at', 'deleted_at', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['is_deleted'], 'default', 'value' => 0],
            [['category_id', 'author_id', 'title', 'slug'], 'required'],
            [['category_id', 'author_id', 'status', 'published_at', 'view_count', 'is_deleted', 'deleted_at', 'created_at', 'updated_at'], 'integer'],
            [['description', 'content'], 'string'],
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
            'category_id' => 'Category ID',
            'author_id' => 'Author ID',
            'title' => 'Title',
            'description' => 'Description',
            'slug' => 'Slug',
            'content' => 'Content',
            'status' => 'Status',
            'published_at' => 'Published At',
            'view_count' => 'View Count',
            'is_deleted' => 'Is Deleted',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}

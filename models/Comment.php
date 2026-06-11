<?php

namespace app\models;

use app\models\base\BaseComment;
use yii\behaviors\TimestampBehavior;

class Comment extends BaseComment
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function fields()
    {
        return parent::fields();
    }
    public function extraFields()
    {
        return [
            'post',
            'author',
            'replies',
        ];
    }

    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public static function find()
    {
        return new \app\models\query\CommentQuery(get_called_class());
    }

    public function getReplies()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])
            ->andWhere(['status' => self::STATUS_ACTIVE]);
    }
}

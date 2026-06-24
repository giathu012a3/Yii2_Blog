<?php

namespace app\models;

use app\helpers\CacheHelper;
use app\models\base\BaseComment;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(Yii::$app->cache, [CacheHelper::getPostId($this->id)]);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        TagDependency::invalidate(Yii::$app->cache, [CacheHelper::getPostId($this->id)]);
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

<?php
namespace app\models;

use app\models\base\BaseCategory;
use app\behaviors\SlugBehavior;
use app\helpers\CacheHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

class Category extends BaseCategory
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public function behaviors()
    {
        return [
            SlugBehavior::class,
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
        return [
            'id',
            'name',
            'status',
            'slug',
            'created_at',
            'updated_at',
        ];
    }
}

<?php

namespace app\models;

use app\models\base\BaseTag;
use app\behaviors\SlugBehavior;
use app\helpers\CacheHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

class Tag extends BaseTag
{
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

    public function beforeValidate()
    {
        if ($this->name !== null) {
            $this->name = mb_strtolower(trim($this->name), 'UTF-8');
        }
        return parent::beforeValidate();
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ];
    }
}

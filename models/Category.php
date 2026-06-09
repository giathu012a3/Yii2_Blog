<?php
namespace app\models;

use app\models\base\BaseCategory;
use app\behaviors\SlugBehavior;
use yii\behaviors\TimestampBehavior;

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

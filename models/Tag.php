<?php

namespace app\models;

use app\models\base\BaseTag;
use app\behaviors\SlugBehavior;
use yii\behaviors\TimestampBehavior;

class Tag extends BaseTag
{
    public function behaviors()
    {
        return [
            SlugBehavior::class,
            TimestampBehavior::class,
        ];
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

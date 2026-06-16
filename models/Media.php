<?php

namespace app\models;

use app\models\base\BaseMedia;
use yii\behaviors\TimestampBehavior;

class Media extends BaseMedia
{
    public function behaviors()
    {
        return [
           [
            'class' => TimestampBehavior::class,
            'updatedAtAttribute' => false,
           ]
        ];
    }
    public function fields()
    {
        return [
            'id',
            'model_id',
            'model_name',
            'url',
            'collection',
            'file_size',
            'mime_type',
            'created_at',
        ];
    }
}

<?php

namespace app\modules\api\models\forms;

use yii\base\Model;

class UploadForm extends Model
{
    public $files;

    public function rules()
    {
        return [
            [['files'], 'required'],
            [['files'], 'file',
                'extensions' => 'png, jpg, jpeg, webp',
                'maxSize' => 5 * 1024 * 1024,
                'maxFiles' => 10,
            ],
        ];
    }
}

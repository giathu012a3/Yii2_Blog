<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use yii\base\Model;

class UploadForm extends Model
{
    public $file;

    public function rules(): array
    {
        return [
            [['file'], 'required'],
            [
                ['file'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => 'png, jpg, jpeg, webp',
                'maxSize' => 5 * 1024 * 1024,
                'mimeTypes' => 'image/png, image/jpeg, image/webp'
            ],
        ];
    }
}

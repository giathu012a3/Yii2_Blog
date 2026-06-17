<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use yii\base\Model;

class AiRequestForm extends Model
{
    public $prompt;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['prompt'], 'required'],
            [['prompt'], 'string'],
        ];
    }
}

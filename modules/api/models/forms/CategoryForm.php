<?php
namespace app\modules\api\models\forms;

use app\models\Category;

class CategoryForm extends Category
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ]);
    }
}

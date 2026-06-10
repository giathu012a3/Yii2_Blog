<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Category;

class CategoryForm extends Category
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'unique', 'filter' => ['is_deleted' => 0], 'when' => function ($model) {
                return $model->isNewRecord || $model->isAttributeChanged('name');
            }],
        ]);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }

        $this->is_deleted = 0;

        return parent::save(false, $attributeNames);
    }
}

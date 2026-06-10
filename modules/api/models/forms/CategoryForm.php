<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Category;

class CategoryForm extends Category
{
    public function rules()
    {
        $rules = [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];

        if ($this->isNewRecord || $this->isAttributeChanged('name')) {
            $rules[] = [['name'], 'unique', 'targetClass' => Category::class, 'filter' => function ($query) {
                $query->andWhere(['is_deleted' => 0]);
            }];
        }

        return $rules;
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

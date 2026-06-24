<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Category;

class CategoryForm extends Category
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'unique', 'filter' => ['is_deleted' => 0]],
            [['name'], 'validateHasChanges', 'skipOnEmpty' => false],
        ]);
    }

    public function load($data, $formName = null): bool
    {
        if (is_array($data)) {
            unset($data['is_deleted'], $data['deleted_at']);
        }
        return parent::load($data, $formName);
    }

    public function validateHasChanges($attribute, $params)
    {
        if ($this->isNewRecord) {
            return;
        }

        $dirty = $this->getDirtyAttributes();
        unset($dirty['updated_at']);

        if (empty($dirty)) {
            $this->addError($attribute, \Yii::t('app', 'No changes detected.'));
        }
    }
}

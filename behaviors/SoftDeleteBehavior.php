<?php

declare(strict_types=1);

namespace app\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class SoftDeleteBehavior extends Behavior
{
    public $isDeletedAttribute = 'is_deleted';
    public $deletedAtAttribute = 'deleted_at';

    public function softDelete()
    {
        $model = $this->owner;
        $model->{$this->isDeletedAttribute} = 1;

        if ($this->deletedAtAttribute !== null) {
            $model->{$this->deletedAtAttribute} = time();
        }

        return $model->save(false);
    }
}

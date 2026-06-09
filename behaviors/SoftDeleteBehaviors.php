<?php

namespace app\behaviors;

use yii\base\Behavior;

class SoftDeleteBehaviors extends Behavior
{
    public $deletedAttribute = 'is_deleted';
    public $deletedAtAttribute = 'deleted_at';

    public function softDelete(): bool
    {
        $model = $this->owner;

        if (!$model->hasAttribute($this->deletedAttribute)) {
            return false;
        }

        $attributes = [$this->deletedAttribute];
        $model->{$this->deletedAttribute} = 1;

        if ($this->deletedAtAttribute && $model->hasAttribute($this->deletedAtAttribute)) {
            $model->{$this->deletedAtAttribute} = time();
            $attributes[] = $this->deletedAtAttribute;
        }

        return $model->save(false, $attributes);
    }
}

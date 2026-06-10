<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\CategoryBase;
use app\models\query\CategoryQuery;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Category model extending CategoryBase.
 */
class Category extends CategoryBase
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
            ],
            [
                'class' => \app\behaviors\SoftDeleteBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): CategoryQuery
    {
        return new CategoryQuery(static::class);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['category_id' => 'id']);
    }
}

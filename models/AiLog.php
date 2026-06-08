<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\AiLogBase;
use app\models\query\AiLogQuery;
use yii\behaviors\TimestampBehavior;

/**
 * AiLog model extending AiLogBase.
 */
class AiLog extends AiLogBase
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): AiLogQuery
    {
        return new AiLogQuery(static::class);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}

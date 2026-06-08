<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\MediaBase;
use app\models\query\MediaQuery;
use yii\behaviors\TimestampBehavior;

/**
 * Media model extending MediaBase.
 */
class Media extends MediaBase
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
    public static function find(): MediaQuery
    {
        return new MediaQuery(static::class);
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

    /**
     * Gets query for [[MediaLinks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMediaLinks()
    {
        return $this->hasMany(MediaLink::class, ['media_id' => 'id']);
    }
}

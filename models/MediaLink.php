<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\MediaLinkBase;
use app\models\query\MediaLinkQuery;

/**
 * MediaLink model extending MediaLinkBase.
 */
class MediaLink extends MediaLinkBase
{
    /**
     * {@inheritdoc}
     */
    public static function find(): MediaLinkQuery
    {
        return new MediaLinkQuery(static::class);
    }

    /**
     * Gets query for [[Media]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedia()
    {
        return $this->hasOne(Media::class, ['id' => 'media_id']);
    }
}

<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\TagBase;
use app\models\query\TagQuery;
use app\behaviors\SoftDeleteBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Tag model extending TagBase.
 */
class Tag extends TagBase
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
                'class' => SoftDeleteBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): TagQuery
    {
        return new TagQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!$insert && isset($changedAttributes['is_deleted']) && (int)$this->is_deleted === 1) {
            PostTag::deleteAll(['tag_id' => $this->id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return ['id', 'name', 'slug'];
    }

    /**
     * Gets query for [[PostTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostTags()
    {
        return $this->hasMany(PostTag::class, ['tag_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->via('postTags');
    }
}

<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "daily_post_stats".
 *
 * @property string $date
 * @property int $posts_count
 * @property int $comments_count
 * @property int $likes_count
 * @property int $views_count
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class DailyPostStats extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%daily_post_stats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['date'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['date'], 'unique'],
            [['posts_count', 'comments_count', 'likes_count', 'views_count'], 'integer', 'min' => 0],
            [['posts_count', 'comments_count', 'likes_count', 'views_count'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'date' => 'Date',
            'posts_count' => 'Posts Count',
            'comments_count' => 'Comments Count',
            'likes_count' => 'Likes Count',
            'views_count' => 'Views Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return [
            'date',
            'posts_count',
            'comments_count',
            'likes_count',
            'views_count',
            'created_at',
            'updated_at',
        ];
    }
}

<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "ai_log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $action
 * @property int|null $prompt_size
 * @property int|null $response_size
 * @property int|null $status
 * @property int|null $duration
 * @property int|null $created_at
 */
class BaseAiLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ai_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'action', 'prompt_size', 'response_size', 'status', 'duration', 'created_at'], 'default', 'value' => null],
            [['user_id', 'prompt_size', 'response_size', 'status', 'duration', 'created_at'], 'integer'],
            [['action'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'action' => 'Action',
            'prompt_size' => 'Prompt Size',
            'response_size' => 'Response Size',
            'status' => 'Status',
            'duration' => 'Duration',
            'created_at' => 'Created At',
        ];
    }

}

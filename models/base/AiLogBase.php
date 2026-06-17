<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "ai_log".
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property int $prompt_size
 * @property int $response_size
 * @property int $status
 * @property float $execution_time
 * @property int|null $created_at
 */
class AiLogBase extends \yii\db\ActiveRecord
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
            [['created_at'], 'default', 'value' => null],
            [['execution_time'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],
            [['action'], 'required'],
            [['user_id', 'prompt_size', 'response_size', 'status', 'created_at'], 'integer'],
            [['execution_time'], 'number'],
            [['action'], 'string', 'max' => 50],
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
            'execution_time' => 'Execution Time',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\AiLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\AiLogQuery(get_called_class());
    }
}

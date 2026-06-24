<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ai_log}}`.
 */
class m260617_023035_create_ai_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ai_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'action' => $this->string(),
            'prompt_size' => $this->integer(),
            'response_size' => $this->integer(),
            'status' => $this->integer(),
            'duration' => $this->integer(),
            'created_at' => $this->integer(),
        ]);

        $this->createIndex('idx-ai_log-user_id', '{{%ai_log}}', 'user_id');
        $this->createIndex('idx-ai_log-action', '{{%ai_log}}', 'action');
        $this->createIndex('idx-ai_log-status', '{{%ai_log}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ai_log}}');
    }
}

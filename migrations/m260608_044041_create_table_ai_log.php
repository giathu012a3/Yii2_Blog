<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `ai_log`.
 */
class m260608_044041_create_table_ai_log extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%ai_log}}', [
                'id'             => $this->primaryKey()->unsigned(),
                'user_id'        => $this->integer()->unsigned()->notNull(),
                'action'         => $this->string(50)->notNull(),
                'prompt_size'    => $this->integer()->notNull()->defaultValue(0),
                'response_size'  => $this->integer()->notNull()->defaultValue(0),
                'status'         => $this->tinyInteger()->notNull()->defaultValue(1), // 1: Success, 0: Failed
                'execution_time' => $this->float()->notNull()->defaultValue(0),
                'created_at'     => $this->integer()->null(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%ai_log}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-ai_log-user_id', '{{%ai_log}}', 'user_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-ai_log-user_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-ai_log-status', '{{%ai_log}}', 'status');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-ai_log-status: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%ai_log}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%ai_log}}: " . $e->getMessage() . "\n";
        }
    }
}

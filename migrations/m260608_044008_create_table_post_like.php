<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `post_like`.
 */
class m260608_044008_create_table_post_like extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%post_like}}', [
                'post_id'    => $this->integer()->unsigned()->notNull(),
                'user_id'    => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->integer()->null(),
                'PRIMARY KEY(post_id, user_id)',
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%post_like}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post_like-post_id', '{{%post_like}}', 'post_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post_like-post_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post_like-user_id', '{{%post_like}}', 'user_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post_like-user_id: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%post_like}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%post_like}}: " . $e->getMessage() . "\n";
        }
    }
}

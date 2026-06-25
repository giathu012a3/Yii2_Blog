<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `comment`.
 */
class m260608_043845_create_table_comment extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%comment}}', [
                'id'         => $this->primaryKey()->unsigned(),
                'post_id'    => $this->integer()->unsigned()->notNull(),
                'user_id'    => $this->integer()->unsigned()->notNull(),
                'parent_id'  => $this->integer()->unsigned()->null(),
                'content'    => $this->text()->notNull(),
                'is_deleted' => $this->tinyInteger()->notNull()->defaultValue(0),
                'deleted_at' => $this->integer()->null(),
                'created_at' => $this->integer()->null(),
                'updated_at' => $this->integer()->null(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%comment}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-comment-post_id', '{{%comment}}', 'post_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-comment-post_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-comment-user_id', '{{%comment}}', 'user_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-comment-user_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-comment-parent_id', '{{%comment}}', 'parent_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-comment-parent_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-comment-is_deleted', '{{%comment}}', 'is_deleted');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-comment-is_deleted: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropIndex('idx-comment-is_deleted', '{{%comment}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] drop idx-comment-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->dropTable('{{%comment}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%comment}}: " . $e->getMessage() . "\n";
        }
    }
}

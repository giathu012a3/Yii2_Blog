<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Migration to add indexes on is_deleted column to optimize queries.
 */
class m260619_023200_add_is_deleted_indexes extends Migration
{
    public function safeUp(): void
    {
        try {
            $this->createIndex('idx-post-is_deleted', '{{%post}}', 'is_deleted');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-comment-is_deleted', '{{%comment}}', 'is_deleted');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-comment-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-tag-is_deleted', '{{%tag}}', 'is_deleted');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-tag-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-category-is_deleted', '{{%category}}', 'is_deleted');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-category-is_deleted: " . $e->getMessage() . "\n";
        }
    }

    public function safeDown(): void
    {
        try {
            $this->dropIndex('idx-post-is_deleted', '{{%post}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] drop idx-post-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->dropIndex('idx-comment-is_deleted', '{{%comment}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] drop idx-comment-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->dropIndex('idx-tag-is_deleted', '{{%tag}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] drop idx-tag-is_deleted: " . $e->getMessage() . "\n";
        }

        try {
            $this->dropIndex('idx-category-is_deleted', '{{%category}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] drop idx-category-is_deleted: " . $e->getMessage() . "\n";
        }
    }
}

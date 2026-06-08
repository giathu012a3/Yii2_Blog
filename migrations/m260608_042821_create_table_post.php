<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `post`.
 */
class m260608_042821_create_table_post extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%post}}', [
                'id'           => $this->primaryKey()->unsigned(),
                'title'        => $this->string(255)->notNull(),
                'content'      => $this->text()->notNull(),
                'slug'         => $this->string(255)->null(),
                'status'       => $this->smallInteger()->notNull()->defaultValue(1),
                'view_count'   => $this->integer()->notNull()->defaultValue(0),
                'category_id'  => $this->integer()->unsigned()->notNull(),
                'author_id'    => $this->integer()->unsigned()->notNull(),
                'published_at' => $this->integer()->null(),
                'is_deleted'   => $this->tinyInteger()->notNull()->defaultValue(0),
                'deleted_at'   => $this->integer()->null(),
                'created_at'   => $this->integer()->null(),
                'updated_at'   => $this->integer()->null(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%post}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post-slug', '{{%post}}', 'slug', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post-slug: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post-status', '{{%post}}', 'status');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post-status: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post-category_id', '{{%post}}', 'category_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post-category_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post-author_id', '{{%post}}', 'author_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post-author_id: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%post}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%post}}: " . $e->getMessage() . "\n";
        }
    }
}

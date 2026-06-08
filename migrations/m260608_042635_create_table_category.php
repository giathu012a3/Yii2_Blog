<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `category`.
 */
class m260608_042635_create_table_category extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%category}}', [
                'id'         => $this->primaryKey()->unsigned(),
                'name'       => $this->string(255)->notNull(),
                'slug'       => $this->string(255)->null(),
                'is_deleted' => $this->tinyInteger()->notNull()->defaultValue(0),
                'deleted_at' => $this->integer()->null(),
                'created_at' => $this->integer()->null(),
                'updated_at' => $this->integer()->null(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%category}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-category-slug', '{{%category}}', 'slug', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-category-slug: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%category}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%category}}: " . $e->getMessage() . "\n";
        }
    }
}

<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `tag`.
 */
class m260608_043202_create_table_tag extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%tag}}', [
                'id'         => $this->primaryKey()->unsigned(),
                'name'       => $this->string(255)->notNull(),
                'slug'       => $this->string(255)->null(),
                'is_deleted' => $this->tinyInteger()->notNull()->defaultValue(0),
                'deleted_at' => $this->integer()->null(),
                'created_at' => $this->integer()->null(),
                'updated_at' => $this->integer()->null(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%tag}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-tag-name', '{{%tag}}', 'name', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-tag-name: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-tag-slug', '{{%tag}}', 'slug', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-tag-slug: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%tag}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%tag}}: " . $e->getMessage() . "\n";
        }
    }
}

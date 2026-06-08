<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `media_link`.
 */
class m260608_044727_create_table_media_link extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%media_link}}', [
                'id'         => $this->primaryKey()->unsigned(),
                'media_id'   => $this->integer()->unsigned()->notNull(),
                'model_type' => $this->string(100)->notNull(),
                'model_id'   => $this->integer()->unsigned()->notNull(),
                'group_type' => $this->string(50)->notNull()->defaultValue('default'),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%media_link}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-media_link-media_id', '{{%media_link}}', 'media_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-media_link-media_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex(
                'idx-media_link-model_group',
                '{{%media_link}}',
                ['model_type', 'model_id', 'group_type']
            );
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-media_link-model_group: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-media_link-group_type', '{{%media_link}}', 'group_type');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-media_link-group_type: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%media_link}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%media_link}}: " . $e->getMessage() . "\n";
        }
    }
}

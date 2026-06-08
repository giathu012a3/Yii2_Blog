<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `media`.
 */
class m260608_044708_create_table_media extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%media}}', [
                'id'         => $this->primaryKey()->unsigned(),
                'user_id'    => $this->integer()->unsigned()->notNull(),
                'file_name'  => $this->string(255)->notNull(),
                'file_url'   => $this->string(255)->notNull(),
                'mime_type'  => $this->string(50)->notNull(),
                'size'       => $this->integer()->notNull(),
                'created_at' => $this->integer()->null(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%media}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-media-user_id', '{{%media}}', 'user_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-media-user_id: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%media}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%media}}: " . $e->getMessage() . "\n";
        }
    }
}

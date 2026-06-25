<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `user_token`.
 *
 * Lưu trữ các token đăng nhập của người dùng tách biệt khỏi bảng `user`.
 * Hỗ trợ đa thiết bị (nhiều token cùng lúc) và theo dõi thời hạn token.
 */
class m260625_080000_create_table_user_token extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%user_token}}', [
                'id'         => $this->primaryKey()->unsigned(),
                'user_id'    => $this->integer()->unsigned()->notNull(),
                'token'      => $this->string(255)->notNull(),
                'expired_at' => $this->integer()->null()->comment('Unix timestamp hết hạn. NULL = không hết hạn.'),
                'created_at' => $this->integer()->notNull(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%user_token}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->addForeignKey(
                'fk-user_token-user_id',
                '{{%user_token}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        } catch (\Throwable $e) {
            echo "    [SKIP] fk-user_token-user_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user_token-token', '{{%user_token}}', 'token', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user_token-token: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user_token-user_id', '{{%user_token}}', 'user_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user_token-user_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user_token-expired_at', '{{%user_token}}', 'expired_at');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user_token-expired_at: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropForeignKey('fk-user_token-user_id', '{{%user_token}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] drop fk-user_token-user_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->dropTable('{{%user_token}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%user_token}}: " . $e->getMessage() . "\n";
        }
    }
}

<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Tạo bảng `user`.
 *
 * Mỗi bước (createTable, createIndex) được bọc trong try-catch riêng.
 * Nếu một bước lỗi (ví dụ bảng đã tồn tại), bước đó sẽ được bỏ qua
 * và ghi log warning — các migration tiếp theo vẫn chạy bình thường.
 */
class m260608_041433_create_table_users extends Migration
{
    public function up(): void
    {
        try {
            $this->createTable('{{%user}}', [
                'id'            => $this->primaryKey()->unsigned(),
                'username'      => $this->string(255)->notNull(),
                'email'         => $this->string(255)->notNull(),
                'auth_key'      => $this->string(32)->notNull(),
                'password_hash' => $this->string(255)->notNull(),
                'access_token'  => $this->string(255)->null(),
                'status'        => $this->smallInteger()->notNull()->defaultValue(1),
                'is_deleted'    => $this->tinyInteger()->notNull()->defaultValue(0),
                'deleted_at'    => $this->integer()->null(),
                'created_at'    => $this->integer()->notNull(),
                'updated_at'    => $this->integer()->notNull(),
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%user}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user-email', '{{%user}}', 'email', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user-email: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user-access_token', '{{%user}}', 'access_token', true);
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user-access_token: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user-status', '{{%user}}', 'status');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user-status: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-user-username', '{{%user}}', 'username');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-user-username: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        try {
            $this->dropTable('{{%user}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%user}}: " . $e->getMessage() . "\n";
        }
    }
}

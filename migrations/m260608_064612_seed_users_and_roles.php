<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Seed tài khoản Admin và khởi tạo 3 vai trò RBAC cơ bản (admin, author, reader).
 */
class m260608_064612_seed_users_and_roles extends Migration
{
    /**
     * @throws \yii\base\Exception
     */
    public function safeUp(): void
    {
        $currentTime = time();
        $passwordHash = Yii::$app->security->generatePasswordHash('admin123456');
        $authKey = Yii::$app->security->generateRandomString();

        $this->insert('{{%user}}', [
            'username'      => 'admin_root',
            'email'         => 'admin@example.com',
            'auth_key'      => $authKey,
            'password_hash' => $passwordHash,
            'access_token'  => Yii::$app->security->generateRandomString(40),
            'status'        => 10,
            'is_deleted'    => 0,
            'created_at'    => $currentTime,
            'updated_at'    => $currentTime,
        ]);

        $adminUserId = $this->db->getLastInsertID();

        $auth = Yii::$app->authManager;

        if ($auth !== null) {
            $auth->removeAll(); 

            $adminRole  = $auth->createRole('admin');
            $authorRole = $auth->createRole('author');
            $readerRole = $auth->createRole('reader');

            $auth->add($adminRole);
            $auth->add($authorRole);
            $auth->add($readerRole);

            $auth->assign($adminRole, $adminUserId);
        }

        echo "    > [SUCCESS] Đã gieo hạt (Seed) thành công 3 Roles và 1 tài khoản Admin!\n";
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        $user = (new \yii\db\Query())
            ->select(['id'])
            ->from('{{%user}}')
            ->where(['username' => 'admin_root'])
            ->one($this->db);

        if ($user) {
            $userId = $user['id'];
            if ($auth !== null) {
                $auth->revokeAll($userId);
            }
            $this->delete('{{%user}}', ['id' => $userId]);
        }

        if ($auth !== null) {
            $auth->removeAll();
        }
    }
}

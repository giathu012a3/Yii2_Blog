<?php

declare(strict_types=1);

use yii\db\Migration;

class m260612_034800_seed_authors extends Migration
{
    public function safeUp(): void
    {
        $currentTime = time();
        $auth = Yii::$app->authManager;

        $users = [
            [
                'username' => 'author_one',
                'email' => 'author_one@example.com',
                'password' => 'author123456',
                'role' => 'author',
            ],
            [
                'username' => 'author_two',
                'email' => 'author_two@example.com',
                'password' => 'author123456',
                'role' => 'author',
            ],
            [
                'username' => 'reader_one',
                'email' => 'reader_one@example.com',
                'password' => 'reader123456',
                'role' => 'reader',
            ],
        ];

        foreach ($users as $userData) {
            try {
                $passwordHash = Yii::$app->security->generatePasswordHash($userData['password']);
                $this->insert('{{%user}}', [
                    'username'      => $userData['username'],
                    'email'         => $userData['email'],
                    'auth_key'      => Yii::$app->security->generateRandomString(),
                    'password_hash' => $passwordHash,
                    'access_token'  => Yii::$app->security->generateRandomString(40),
                    'status'        => 1,
                    'is_deleted'    => 0,
                    'created_at'    => $currentTime,
                    'updated_at'    => $currentTime,
                ]);

                $userId = $this->db->getLastInsertID();
                if ($auth !== null) {
                    $role = $auth->getRole($userData['role']);
                    if ($role !== null) {
                        $auth->assign($role, $userId);
                    }
                }
            } catch (\Throwable $e) {
                echo "    [SKIP] {$userData['username']}: " . $e->getMessage() . "\n";
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $usernames = ['author_one', 'author_two', 'reader_one'];

        foreach ($usernames as $username) {
            $user = (new \yii\db\Query())
                ->select(['id'])
                ->from('{{%user}}')
                ->where(['username' => $username])
                ->one($this->db);

            if ($user) {
                $userId = $user['id'];
                if ($auth !== null) {
                    try {
                        $auth->revokeAll($userId);
                    } catch (\Throwable $e) {
                        echo "    [SKIP] revokeAll $username: " . $e->getMessage() . "\n";
                    }
                }
                $this->delete('{{%user}}', ['id' => $userId]);
            }
        }
    }
}

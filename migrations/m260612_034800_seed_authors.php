<?php

declare(strict_types=1);

use yii\db\Migration;

class m260612_034800_seed_authors extends Migration
{
    public function safeUp(): void
    {
        $currentTime = time();
        $passwordHash = Yii::$app->security->generatePasswordHash('author123456');
        $auth = Yii::$app->authManager;

        if ($auth === null) {
            return;
        }

        $authorRole = $auth->getRole('author');
        if ($authorRole === null) {
            return;
        }

        try {
            $this->insert('{{%user}}', [
                'username'      => 'author_one',
                'email'         => 'author_one@example.com',
                'auth_key'      => Yii::$app->security->generateRandomString(),
                'password_hash' => $passwordHash,
                'access_token'  => Yii::$app->security->generateRandomString(40),
                'status'        => 1,
                'is_deleted'    => 0,
                'created_at'    => $currentTime,
                'updated_at'    => $currentTime,
            ]);
            $authorOneId = $this->db->getLastInsertID();
            $auth->assign($authorRole, $authorOneId);
        } catch (\Throwable $e) {
            echo "    [SKIP] author_one: " . $e->getMessage() . "\n";
        }

        try {
            $this->insert('{{%user}}', [
                'username'      => 'author_two',
                'email'         => 'author_two@example.com',
                'auth_key'      => Yii::$app->security->generateRandomString(),
                'password_hash' => $passwordHash,
                'access_token'  => Yii::$app->security->generateRandomString(40),
                'status'        => 1,
                'is_deleted'    => 0,
                'created_at'    => $currentTime,
                'updated_at'    => $currentTime,
            ]);
            $authorTwoId = $this->db->getLastInsertID();
            $auth->assign($authorRole, $authorTwoId);
        } catch (\Throwable $e) {
            echo "    [SKIP] author_two: " . $e->getMessage() . "\n";
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        foreach (['author_one', 'author_two'] as $username) {
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

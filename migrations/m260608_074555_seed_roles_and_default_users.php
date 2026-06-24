<?php

use app\rbac\Permission;
use yii\db\Migration;
use yii\db\Query;

class m260608_074555_seed_roles_and_default_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $admin = $auth->createRole(Permission::ROLE_ADMIN);
        $auth->add($admin);

        $author = $auth->createRole(Permission::ROLE_AUTHOR);
        $auth->add($author);

        $reader = $auth->createRole(Permission::ROLE_READER);
        $auth->add($reader);

        // Add admin
        $time = time();

        $adminUserId = (new Query())
            ->select(['id'])
            ->from('user')
            ->where(['username' => 'admin'])
            ->scalar();

        if (!$adminUserId) {
            $this->insert('user', [
                'username' => 'admin',
                'email' => 'admin@sos.com',
                'password_hash' => Yii::$app->security->generatePasswordHash('Admin@123'),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'status' => 1,
                'created_at' => $time,
                'updated_at' => $time,
            ]);

            $adminUserId = $this->db->getLastInsertID();
        }

        if ($adminUserId) {
            $auth->assign($admin, $adminUserId);
        }

        // Add user
        $readerUserId = (new Query())
            ->select(['id'])
            ->from('user')
            ->where(['username' => 'reader'])
            ->scalar();

        if (!$readerUserId) {
            $this->insert('user', [
                'username' => 'reader',
                'email' => 'reader@sos.com',
                'password_hash' => Yii::$app->security->generatePasswordHash('Reader@123'),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'status' => 1,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            $readerUserId = $this->db->getLastInsertID();
        }

        if ($readerUserId) {
            $auth->assign($reader, $readerUserId);
        }

        // Add author 1
        $author1UserId = (new Query())
            ->select(['id'])
            ->from('user')
            ->where(['username' => 'author1'])
            ->scalar();

        if (!$author1UserId) {
            $this->insert('user', [
                'username' => 'author1',
                'email' => 'author1@sos.com',
                'password_hash' => Yii::$app->security->generatePasswordHash('Author@123'),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'status' => 1,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            $author1UserId = $this->db->getLastInsertID();
        }

        if ($author1UserId) {
            $auth->assign($author, $author1UserId);
        }

        // Add author 2
        $author2UserId = (new Query())
            ->select(['id'])
            ->from('user')
            ->where(['username' => 'author2'])
            ->scalar();

        if (!$author2UserId) {
            $this->insert('user', [
                'username' => 'author2',
                'email' => 'author2@sos.com',
                'password_hash' => Yii::$app->security->generatePasswordHash('Author@123'),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'status' => 1,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            $author2UserId = $this->db->getLastInsertID();
        }

        if ($author2UserId) {
            $auth->assign($author, $author2UserId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $this->delete('user', ['username' => 'admin']);
        $this->delete('user', ['username' => 'reader']);
        $this->delete('user', ['username' => 'author1']);
        $this->delete('user', ['username' => 'author2']);
    }
}

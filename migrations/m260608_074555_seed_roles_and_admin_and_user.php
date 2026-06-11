<?php

use app\rbac\Permission;
use yii\db\Migration;
use yii\db\Query;

class m260608_074555_seed_roles_and_admin_and_user extends Migration
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
    }
}

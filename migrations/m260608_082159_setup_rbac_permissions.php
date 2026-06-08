<?php

use app\rbac\AuthorRule;
use yii\db\Migration;

class m260608_082159_setup_rbac_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $rule = new AuthorRule();
        $auth->add($rule);

        //post
        $createPost = $auth->createPermission('createPost');
        $auth->add($createPost);

        $updatePost = $auth->createPermission('updatePost');
        $auth->add($updatePost);

        $deletePost = $auth->createPermission('deletePost');
        $auth->add($deletePost);

        //category and tag
        $manageCatgories = $auth->createPermission('manageCategories');
        $auth->add($manageCatgories);

        $manageTags = $auth->createPermission('manageTags');
        $auth->add($manageTags);

        // add rule
        $updateOwnPost = $auth->createPermission('updateOwnPost');
        $updateOwnPost->ruleName = $rule->name;
        $auth->add($updateOwnPost);

        $deleteOwnPost = $auth->createPermission('deleteOwnPost');
        $deleteOwnPost->ruleName = $rule->name;
        $auth->add($deleteOwnPost);

        $auth->addChild($updateOwnPost, $updatePost);
        $auth->addChild($deleteOwnPost, $deletePost);


        $admin = $auth->getRole('admin');
        $author = $auth->getRole('author');
        $reader = $auth->getRole('reader');

        // add permission for author
        $auth->addChild($author, $createPost);
        $auth->addChild($author, $updateOwnPost);
        $auth->addChild($author, $deleteOwnPost);
        $auth->addChild($author, $manageTags);

        // add permission for admin
        $auth->addChild($admin, $createPost);
        $auth->addChild($admin, $updatePost);
        $auth->addChild($admin, $deletePost);
        $auth->addChild($admin, $manageCatgories);
        $auth->addChild($admin, $manageTags);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $permissions = [
            'createPost',
            'updatePost',
            'deletePost',
            'manageCategories',
            'manageTags',
            'updateOwnPost',
            'deleteOwnPost'
        ];

        //remove permission
        foreach ($permissions as $name) {
            $permission = $auth->getPermission($name);
            if ($permission) {
                $auth->remove($permission);
            }
        }

        //remove rule
        $rule = $auth->getRule('isAuthor');
        if ($rule) {
            $auth->remove($rule);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_082159_setup_rbac_permissions cannot be reverted.\n";

        return false;
    }
    */
}

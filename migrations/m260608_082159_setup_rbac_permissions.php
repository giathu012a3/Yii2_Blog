<?php

use app\rbac\AuthorRule;
use app\rbac\Permission;
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

        // Role-specific access permissions
        $adminAccess = $auth->createPermission(Permission::ADMIN_ACCESS);
        $auth->add($adminAccess);

        $authorAccess = $auth->createPermission(Permission::AUTHOR_ACCESS);
        $auth->add($authorAccess);

        $readerAccess = $auth->createPermission(Permission::READER_ACCESS);
        $auth->add($readerAccess);

        // Extra administrative permissions
        $manageUsers = $auth->createPermission(Permission::MANAGE_USERS);
        $auth->add($manageUsers);

        $manageRoles = $auth->createPermission(Permission::MANAGE_ROLES);
        $auth->add($manageRoles);

        // Post permissions
        $createPost = $auth->createPermission(Permission::CREATE_POST);
        $auth->add($createPost);

        $updatePost = $auth->createPermission(Permission::UPDATE_POST);
        $auth->add($updatePost);

        $deletePost = $auth->createPermission(Permission::DELETE_POST);
        $auth->add($deletePost);

        // Category and tag
        $manageCatgories = $auth->createPermission(Permission::MANAGE_CATEGORIES);
        $auth->add($manageCatgories);

        $manageTags = $auth->createPermission(Permission::MANAGE_TAGS);
        $auth->add($manageTags);

        // Add rule post permissions
        $updateOwnPost = $auth->createPermission(Permission::UPDATE_OWN_POST);
        $updateOwnPost->ruleName = $rule->name;
        $auth->add($updateOwnPost);

        $deleteOwnPost = $auth->createPermission(Permission::DELETE_OWN_POST);
        $deleteOwnPost->ruleName = $rule->name;
        $auth->add($deleteOwnPost);

        $auth->addChild($updateOwnPost, $updatePost);
        $auth->addChild($deleteOwnPost, $deletePost);

        // Comment permissions
        $updateOwnComment = $auth->createPermission(Permission::UPDATE_OWN_COMMENT);
        $updateOwnComment->ruleName = $rule->name;
        $auth->add($updateOwnComment);

        $deleteOwnComment = $auth->createPermission(Permission::DELETE_OWN_COMMENT);
        $deleteOwnComment->ruleName = $rule->name;
        $auth->add($deleteOwnComment);

        $hideCommentOnOwnPost = $auth->createPermission(Permission::HIDE_COMMENT_ON_OWN_POST);
        $hideCommentOnOwnPost->ruleName = $rule->name;
        $auth->add($hideCommentOnOwnPost);

        $deleteCommentOnOwnPost = $auth->createPermission(Permission::DELETE_COMMENT_ON_OWN_POST);
        $deleteCommentOnOwnPost->ruleName = $rule->name;
        $auth->add($deleteCommentOnOwnPost);

        $admin = $auth->getRole(Permission::ROLE_ADMIN);
        $author = $auth->getRole(Permission::ROLE_AUTHOR);
        $reader = $auth->getRole(Permission::ROLE_READER);

        // Add permission for reader
        $auth->addChild($reader, $readerAccess);
        $auth->addChild($reader, $updateOwnComment);
        $auth->addChild($reader, $deleteOwnComment);

        // Add permission for author
        $auth->addChild($author, $authorAccess);
        $auth->addChild($author, $createPost);
        $auth->addChild($author, $updateOwnPost);
        $auth->addChild($author, $deleteOwnPost);
        // Author inherits reader's permissions
        $auth->addChild($author, $reader);
        // Author also has permissions to manage comments on own post
        $auth->addChild($author, $hideCommentOnOwnPost);
        $auth->addChild($author, $deleteCommentOnOwnPost);

        // Add permission for admin
        $auth->addChild($admin, $adminAccess);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $manageRoles);
        $auth->addChild($admin, $createPost);
        $auth->addChild($admin, $updatePost);
        $auth->addChild($admin, $deletePost);
        $auth->addChild($admin, $manageCatgories);
        $auth->addChild($admin, $manageTags);
        // Admin inherits author's permissions
        $auth->addChild($admin, $author);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $permissions = [
            Permission::ADMIN_ACCESS,
            Permission::AUTHOR_ACCESS,
            Permission::READER_ACCESS,
            Permission::MANAGE_USERS,
            Permission::MANAGE_ROLES,
            Permission::CREATE_POST,
            Permission::UPDATE_POST,
            Permission::DELETE_POST,
            Permission::UPDATE_OWN_POST,
            Permission::DELETE_OWN_POST,
            Permission::MANAGE_CATEGORIES,
            Permission::MANAGE_TAGS,
            Permission::UPDATE_OWN_COMMENT,
            Permission::DELETE_OWN_COMMENT,
            Permission::HIDE_COMMENT_ON_OWN_POST,
            Permission::DELETE_COMMENT_ON_OWN_POST,
        ];

        // Remove permission
        foreach ($permissions as $name) {
            $permission = $auth->getPermission($name);
            if ($permission) {
                $auth->remove($permission);
            }
        }

        // Remove rule
        $rule = $auth->getRule('isAuthor');
        if ($rule) {
            $auth->remove($rule);
        }
    }
}

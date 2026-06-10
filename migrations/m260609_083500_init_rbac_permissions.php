<?php

declare(strict_types=1);

use yii\db\Migration;

class m260609_083500_init_rbac_permissions extends Migration
{
    public function up(): void
    {
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            return;
        }

        try {
            $auth->add(new \app\rbac\AuthorRule());
        } catch (\Throwable $e) {
            echo "    [SKIP] add rule isAuthor: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('manageCategory');
            $p->description = 'Manage category entries (Create, Update, Delete)';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission manageCategory: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('manageTags');
            $p->description = 'Manage tag entries (Create, Update, Delete)';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission manageTags: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('createPost');
            $p->description = 'Create a new post';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission createPost: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('updatePost');
            $p->description = 'Update any post';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission updatePost: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('deletePost');
            $p->description = 'Delete any post';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission deletePost: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('updateOwnPost');
            $p->description = 'Update own post';
            $p->ruleName = 'isAuthor';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission updateOwnPost: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('deleteOwnPost');
            $p->description = 'Delete own post';
            $p->ruleName = 'isAuthor';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission deleteOwnPost: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('createComment');
            $p->description = 'Create a comment';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission createComment: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('updateComment');
            $p->description = 'Update any comment';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission updateComment: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('deleteComment');
            $p->description = 'Delete any comment';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission deleteComment: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('updateOwnComment');
            $p->description = 'Update own comment';
            $p->ruleName = 'isAuthor';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission updateOwnComment: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('deleteOwnComment');
            $p->description = 'Delete own comment';
            $p->ruleName = 'isAuthor';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission deleteOwnComment: " . $e->getMessage() . "\n";
        }

        try {
            $p = $auth->createPermission('likePost');
            $p->description = 'Like or unlike a post';
            $auth->add($p);
        } catch (\Throwable $e) {
            echo "    [SKIP] create permission likePost: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getPermission('updateOwnPost'), $auth->getPermission('updatePost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] addChild updateOwnPost -> updatePost: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getPermission('deleteOwnPost'), $auth->getPermission('deletePost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] addChild deleteOwnPost -> deletePost: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getPermission('updateOwnComment'), $auth->getPermission('updateComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] addChild updateOwnComment -> updateComment: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getPermission('deleteOwnComment'), $auth->getPermission('deleteComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] addChild deleteOwnComment -> deleteComment: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getRole('reader'), $auth->getPermission('createComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] reader -> createComment: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('reader'), $auth->getPermission('updateOwnComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] reader -> updateOwnComment: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('reader'), $auth->getPermission('deleteOwnComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] reader -> deleteOwnComment: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('reader'), $auth->getPermission('likePost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] reader -> likePost: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getRole('author'), $auth->getRole('reader'));
        } catch (\Throwable $e) {
            echo "    [SKIP] author -> reader: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('author'), $auth->getPermission('createPost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] author -> createPost: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('author'), $auth->getPermission('updateOwnPost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] author -> updateOwnPost: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('author'), $auth->getPermission('deleteOwnPost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] author -> deleteOwnPost: " . $e->getMessage() . "\n";
        }

        try {
            $auth->addChild($auth->getRole('admin'), $auth->getRole('author'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> author: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('admin'), $auth->getPermission('manageCategory'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> manageCategory: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('admin'), $auth->getPermission('manageTags'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> manageTags: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('admin'), $auth->getPermission('updatePost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> updatePost: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('admin'), $auth->getPermission('deletePost'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> deletePost: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('admin'), $auth->getPermission('updateComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> updateComment: " . $e->getMessage() . "\n";
        }
        try {
            $auth->addChild($auth->getRole('admin'), $auth->getPermission('deleteComment'));
        } catch (\Throwable $e) {
            echo "    [SKIP] admin -> deleteComment: " . $e->getMessage() . "\n";
        }
    }

    public function down(): void
    {
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            return;
        }

        try {
            $auth->removeAllPermissions();
        } catch (\Throwable $e) {
            echo "    [SKIP] removeAllPermissions: " . $e->getMessage() . "\n";
        }

        try {
            $auth->removeAllRules();
        } catch (\Throwable $e) {
            echo "    [SKIP] removeAllRules: " . $e->getMessage() . "\n";
        }
    }
}

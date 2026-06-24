<?php

declare(strict_types=1);

namespace app\rbac;

class Permission
{
    // Roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_AUTHOR = 'author';
    public const ROLE_READER = 'reader';

    // Role-specific base access permissions
    public const ADMIN_ACCESS = 'adminAccess';
    public const AUTHOR_ACCESS = 'authorAccess';
    public const READER_ACCESS = 'readerAccess';

    // Post Permissions
    public const CREATE_POST = 'createPost';
    public const UPDATE_POST = 'updatePost';
    public const DELETE_POST = 'deletePost';
    public const UPDATE_OWN_POST = 'updateOwnPost';
    public const DELETE_OWN_POST = 'deleteOwnPost';

    // Category / Tag Permissions
    public const MANAGE_CATEGORIES = 'manageCategories';
    public const MANAGE_TAGS = 'manageTags';

    // Comment Permissions
    public const UPDATE_OWN_COMMENT = 'updateOwnComment';
    public const DELETE_OWN_COMMENT = 'deleteOwnComment';
    public const HIDE_COMMENT_ON_OWN_POST = 'hideCommentOnOwnPost';
    public const DELETE_COMMENT_ON_OWN_POST = 'deleteCommentOnOwnPost';

    // Extra administrative permissions
    public const MANAGE_USERS = 'manageUsers';
    public const MANAGE_ROLES = 'manageRoles';
}

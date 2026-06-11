<?php

namespace app\rbac;

use yii\rbac\Rule;

class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    public function execute($user, $item, $params)
    {
        if (isset($params['post'])) {
            return $params['post']->author_id == $user;
        }
        if (isset($params['comment'])) {
            return $params['comment']->author_id == $user;
        }
        return false;
    }
}

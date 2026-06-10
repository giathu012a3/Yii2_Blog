<?php

declare(strict_types=1);

namespace app\rbac;

use yii\rbac\Rule;

/**
 * AuthorRule checks if the user ID matches the owner ID of the model (Post or Comment).
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    /**
     * {@inheritdoc}
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            $model = $params['model'];

            // Post owner checking via author_id
            if (isset($model->author_id)) {
                return (int) $model->author_id === (int) $user;
            }

            // Comment owner checking via user_id
            if (isset($model->user_id)) {
                return (int) $model->user_id === (int) $user;
            }
        }

        return false;
    }
}

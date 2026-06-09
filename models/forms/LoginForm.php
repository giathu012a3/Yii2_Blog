<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;

/**
 * LoginForm handles REST API user login verification and token generation.
 */
class LoginForm extends Model
{
    public ?string $username = null;
    public ?string $password = null;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'trim'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Inline validator for password.
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Authenticates user and generates bearer token.
     *
     * @return User|null the authenticated user with token, or null on failure
     */
    public function login()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = $this->getUser();
        if ($user) {
            $user->generateAccessToken();
            if ($user->save(false)) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Find user by username.
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}

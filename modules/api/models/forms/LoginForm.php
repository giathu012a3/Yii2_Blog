<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public ?string $username = null;
    public ?string $password = null;

    private $_user;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'trim'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('app', 'Incorrect username or password.'));
            }
        }
    }

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

    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}

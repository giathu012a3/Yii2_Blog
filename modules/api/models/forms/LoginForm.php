<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    private const TOKEN_EXPIRE_DURATION = 7 * 24 * 3600; // 7 ngày

    public ?string $username = null;
    public ?string $password = null;

    private $_user = null;

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

    public function login(): ?array
    {
        if (!$this->validate()) {
            return null;
        }

        $user = $this->getUser();
        if ($user === null) {
            return null;
        }

        // Tạo token với thời hạn 7 ngày
        $tokenString = $user->generateAccessToken(self::TOKEN_EXPIRE_DURATION);
        if ($tokenString === null) {
            return null;
        }

        return [
            'user'  => $user,
            'token' => $tokenString,
        ];
    }

    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }
}

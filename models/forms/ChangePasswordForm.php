<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\User;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $current_password;
    public $new_password;
    public $confirm_password;

    private $_user;

    public function __construct(User $user, array $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            [['current_password', 'new_password', 'confirm_password'], 'trim'],
            [['current_password', 'new_password', 'confirm_password'], 'string', 'min' => 6],
            ['current_password', 'validateCurrentPassword'],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match.'],
            ['new_password', 'compare', 'compareAttribute' => 'current_password', 'operator' => '!=', 'message' => 'New password must be different from current password.'],
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user->validatePassword($this->current_password)) {
                $this->addError($attribute, 'Incorrect current password.');
            }
        }
    }

    public function change()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->setPassword($this->new_password);
        $this->_user->revokeAccessToken();

        return $this->_user->save(false);
    }
}

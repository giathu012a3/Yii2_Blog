<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\User;

class ChangePasswordForm extends User
{
    public $current_password;
    public $new_password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['current_password', 'new_password', 'confirm_password'], 'required'],
            [['current_password', 'new_password', 'confirm_password'], 'trim'],
            [['current_password', 'new_password', 'confirm_password'], 'string', 'min' => 6],
            ['current_password', 'validateCurrentPassword'],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => \Yii::t('app', 'Passwords do not match.')],
            ['new_password', 'compare', 'compareAttribute' => 'current_password', 'operator' => '!=', 'message' => \Yii::t('app', 'New password must be different from current password.')],
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->current_password)) {
                $this->addError($attribute, \Yii::t('app', 'Incorrect current password.'));
            }
        }
    }

    public function change(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->setPassword($this->new_password);
        // Thu hồi toàn bộ token khi đổi mật khẩu (buộc đăng nhập lại)
        $this->revokeAllTokens();

        return $this->save(false);
    }
}

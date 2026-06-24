<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\User;
use Yii;

class RegisterForm extends User
{
    public ?string $password = null;

    public function rules()
    {
        $rules = parent::rules();
        return array_merge($rules, [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['email', 'email'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => \Yii::t('app', 'This username has already been taken.')],
            ['email', 'unique', 'targetClass' => User::class, 'message' => \Yii::t('app', 'This email address has already been taken.')],
        ]);
    }

    public function load($data, $formName = null): bool
    {
        if (is_array($data)) {
            unset($data['access_token'], $data['status'], $data['is_deleted'], $data['deleted_at']);
        }
        return parent::load($data, $formName);
    }

    public function register()
    {
        if (!$this->validate()) {
            return null;
        }

        $this->setPassword($this->password);
        $this->generateAuthKey();
        $this->status = self::STATUS_ACTIVE;
        $this->is_deleted = 0;

        if ($this->save(false)) {
            $auth = Yii::$app->authManager;
            if ($auth) {
                $readerRole = $auth->getRole('reader');
                if ($readerRole) {
                    $auth->assign($readerRole, $this->id);
                }
            }
            return $this;
        }

        return null;
    }
}

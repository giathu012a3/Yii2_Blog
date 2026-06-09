<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\User;
use Yii;

/**
 * RegisterForm handles user registration input validation and model saving.
 */
class RegisterForm extends User
{
    public ?string $password = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = parent::rules();
        return array_merge($rules, [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['email', 'email'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],
        ]);
    }

    /**
     * Registers a new user.
     *
     * @return User|null the saved user model, or null if saving failed
     */
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

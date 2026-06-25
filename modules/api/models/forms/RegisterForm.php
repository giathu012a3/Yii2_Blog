<?php

namespace app\modules\api\models\forms;

use app\models\User;
use app\models\UserAccessToken;
use Exception;
use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_confirmation;

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'password_confirmation'], 'required'],
            [['email'], 'email'],
            [['username'], 'string', 'min' => 3, 'max' => 255],
            [['password'], 'string', 'min' => 6, 'max' => 255],
            [['password_confirmation'], 'compare', 'compareAttribute' => 'password'],
            [['username'], 'unique', 'targetClass' => User::class],
            [['email'], 'unique', 'targetClass' => User::class],
        ];
    }


    public function register()
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;

            if ($user->save()) {
                $auth = Yii::$app->authManager;
                $readerRole = $auth->getRole(User::ROLE_READER);
                if ($readerRole) {
                    $auth->assign($readerRole, $user->id);
                }

                $transaction->commit();

                // Send welcome email
                try {
                    Yii::$app->mailer->compose('welcome', ['user' => $user])
                        ->setTo($user->email)
                        ->setSubject('Welcome to Yii2 Blog App')
                        ->send();
                } catch (\Exception $e) {
                    Yii::error('Failed to send welcome email to ' . $user->email . ': ' . $e->getMessage(), 'email');
                }

                return $user;
            }

        }catch(\Exception $e) {
            $transaction->rollback();
            $this->addError('register', Yii::t('app', 'Registration failed.') . $e->getMessage());
        }

        return null;

    }
}

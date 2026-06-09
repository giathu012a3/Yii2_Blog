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

               $accessToken = new UserAccessToken();
               $accessToken->user_id = $user->id;
               $accessToken->token = Yii::$app->security->generateRandomString(64);
               $accessToken->expires_at = time() + 7 * 24 * 3600;

               if(!$accessToken->save()) {
                   throw new Exception('Save access token failed.');
               }

               $transaction->commit();
               $user->access_token = $accessToken->token;
               return $user;
            }

        }catch(\Exception $e) {
            $transaction->rollback();
            $this->addError('register', 'Registration failed.' . $e->getMessage());
        }

        return null;

    }
}

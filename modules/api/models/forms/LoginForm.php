<?php

namespace app\modules\api\models\forms;

use app\models\User;
use app\models\UserAccessToken;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    private const MAX_FAILED_LOGIN_ATTEMPTS = 5;
    private const BLOCK_DURATION = 60;
    private const TOKEN_EXPIRE_DURATION = 7 * 24 * 3600;
    private const CACHE_KEY_BLOCK_PREFIX = 'login_block_';
    private const CACHE_KEY_FAILED_PREFIX = 'login_failed_';

    public $username;
    public $password;

    private $_user = null;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['password'], 'validatePassword']
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('Login error', 'Incorrect username or password.');
            }
        }
    }

    public function login()
    {
        $ip = Yii::$app->request->userIP;

        if($this->isBlocked($ip)) {
            throw new \yii\web\TooManyRequestsHttpException('Too many failed login attempts. Please try again in 1 minute.');
        }

        if ($this->validate()) {
            $user = $this->getUser();

            if ($user) {
                $this->clearFailedLoginAttempts($ip);

                $accessToken = $this->createAccessToken($user);

                if ($accessToken) {
                    $user->currentToken = $accessToken;
                    return $user;
                }
            }
        }

        if(!empty($this->username) && !empty($this->password)) {
            $this->increaseFailedLoginAttempts($ip);
        }

        return null;
    }

    private function increaseFailedLoginAttempts($ip)
    {
        $keyCheck = self::CACHE_KEY_FAILED_PREFIX . $ip;
        $blockKey = self::CACHE_KEY_BLOCK_PREFIX . $ip;

        $loginFailed = (int)Yii::$app->cache->get($keyCheck) + 1;
        Yii::$app->cache->set($keyCheck, $loginFailed, self::BLOCK_DURATION);
        if ($loginFailed >= self::MAX_FAILED_LOGIN_ATTEMPTS) {
            Yii::$app->cache->set($blockKey, 'blocked', self::BLOCK_DURATION);
            Yii::$app->cache->delete($keyCheck);
        }
    }
    private function isBlocked($ip)
    {
        $blockKey = self::CACHE_KEY_BLOCK_PREFIX . $ip;
        return (bool)Yii::$app->cache->get($blockKey);
    }

    private function clearFailedLoginAttempts($ip)
    {
        Yii::$app->cache->delete(self::CACHE_KEY_FAILED_PREFIX . $ip);
    }

    private function createAccessToken($user)
    {
        $accessToken = new UserAccessToken();
        $accessToken->user_id = $user->id;
        $accessToken->token = Yii::$app->security->generateRandomString(64);
        $accessToken->expires_at = time() + self::TOKEN_EXPIRE_DURATION;
        $accessToken->device_name = Yii::$app->request->userAgent;
        if ($accessToken->save(false)) {
            return $accessToken;
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

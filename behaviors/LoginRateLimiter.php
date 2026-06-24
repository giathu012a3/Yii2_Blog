<?php

declare(strict_types=1);

namespace app\behaviors;

use Yii;
use yii\base\ActionFilter;
use yii\web\HttpException;

/**
 * LoginRateLimiter is an action filter that implements failed login rate limiting.
 * If there are 5 login failures within 1 minute from the same IP address,
 * it blocks further login attempts for 1 minute.
 */
class LoginRateLimiter extends ActionFilter
{
    public int $maxAttempts = 5;
    public int $decaySeconds = 60;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        $ip = Yii::$app->request->userIP ?? '127.0.0.1';
        $blockedKey = "login_blocked:{$ip}";

        if (Yii::$app->cache->get($blockedKey)) {
            throw new HttpException(429, 'Too many failed login attempts. Please try again after 1 minute.');
        }

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $ip = Yii::$app->request->userIP ?? '127.0.0.1';
        $blockedKey = "login_blocked:{$ip}";
        $failsKey = "login_fails:{$ip}";

        $response = Yii::$app->response;

        if ($response->statusCode === 422) {
            $fails = (int) Yii::$app->cache->get($failsKey);
            $fails++;
            if ($fails >= $this->maxAttempts) {
                Yii::$app->cache->set($blockedKey, true, $this->decaySeconds);
                Yii::$app->cache->delete($failsKey);
            } else {
                Yii::$app->cache->set($failsKey, $fails, $this->decaySeconds);
            }
        } elseif ($response->isSuccessful) {
            Yii::$app->cache->delete($failsKey);
        }

        return parent::afterAction($action, $result);
    }
}

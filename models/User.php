<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\UserBase;
use app\models\UserToken;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * User model connected to the database.
 */
class User extends UserBase implements IdentityInterface
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;
    const ROLE_ADMIN  = 'admin';
    const ROLE_AUTHOR = 'author';
    const ROLE_READER = 'reader';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $userToken = UserToken::findActive((string) $token);
        if ($userToken === null) {
            return null;
        }

        return static::findOne(['id' => $userToken->user_id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username.
     */
    public static function findByUsername(string $username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'username',
            'email',
            'status',
            'created_at',
            'roles',
        ];
    }

    /**
     * Quan hệ đến các token đăng nhập của user.
     */
    public function getTokens(): \yii\db\ActiveQuery
    {
        return $this->hasMany(UserToken::class, ['user_id' => 'id']);
    }

    /**
     * Lấy danh sách roles của user.
     */
    public function getRoles()
    {
        $auth = Yii::$app->authManager;
        return array_keys($auth->getRolesByUser($this->id));
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Validates password.
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Tạo token mới trong bảng user_token và trả về chuỗi token.
     *
     * @param int|null $ttl Thời hạn tính bằng giây. NULL = không hết hạn.
     * @return string|null  Chuỗi token vừa tạo, hoặc null nếu thất bại.
     */
    public function generateAccessToken(?int $ttl = null): ?string
    {
        $userToken = UserToken::generate((int) $this->id, $ttl);
        return $userToken?->token;
    }

    /**
     * Thu hồi một token cụ thể khỏi bảng user_token.
     *
     * @param string $token Chuỗi token cần thu hồi.
     */
    public function revokeAccessToken(string $token): void
    {
        UserToken::deleteAll(['user_id' => $this->id, 'token' => $token]);
    }

    /**
     * Thu hồi toàn bộ token của user (dùng khi đổi mật khẩu).
     */
    public function revokeAllTokens(): void
    {
        UserToken::revokeAllForUser((int) $this->id);
    }
}

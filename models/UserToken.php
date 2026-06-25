<?php

declare(strict_types=1);

namespace app\models;

use app\models\base\UserTokenBase;
use Yii;

/**
 * UserToken quản lý token đăng nhập của người dùng.
 * Hỗ trợ đa thiết bị và theo dõi thời hạn token.
 */
class UserToken extends UserTokenBase
{
    /**
     * Kiểm tra token có hết hạn chưa.
     * Token có expired_at = NULL không bao giờ hết hạn.
     */
    public function isExpired(): bool
    {
        if ($this->expired_at === null) {
            return false;
        }

        return $this->expired_at < time();
    }

    /**
     * Tạo một token mới cho user và lưu vào DB.
     *
     * @param int $userId
     * @param int|null $ttl  Thời hạn tính bằng giây. NULL = không hết hạn.
     * @return static|null   Trả về object UserToken đã lưu, hoặc null nếu thất bại.
     */
    public static function generate(int $userId, ?int $ttl = null): ?static
    {
        $token = new static();
        $token->user_id    = $userId;
        $token->token      = Yii::$app->security->generateRandomString() . '_' . time();
        $token->expired_at = $ttl !== null ? (time() + $ttl) : null;
        $token->created_at = time();

        if ($token->save(false)) {
            return $token;
        }

        return null;
    }

    /**
     * Tìm token hợp lệ (chưa hết hạn) theo chuỗi token.
     */
    public static function findActive(string $tokenString): ?static
    {
        return static::find()
            ->active()
            ->andWhere(['token' => $tokenString])
            ->one();
    }

    /**
     * Xóa tất cả token của một user (dùng khi đổi mật khẩu, v.v.).
     */
    public static function revokeAllForUser(int $userId): void
    {
        static::deleteAll(['user_id' => $userId]);
    }
}

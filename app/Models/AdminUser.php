<?php

namespace App\Models;

class AdminUser
{
    public static function currentId(): ?int
    {
        $user = self::user();
        $id = (int)($user['id'] ?? 0);

        return $id > 0 ? $id : null;
    }

    public static function verifyPassword(string $password): bool
    {
        $user = self::user();
        if (!$user) {
            return hash_equals(getenv('ADMIN_PASSWORD') ?: 'admin123', $password);
        }

        return password_verify($password, $user['password_hash']);
    }

    public static function changePassword(string $currentPassword, string $newPassword): bool
    {
        if (!self::verifyPassword($currentPassword)) {
            return false;
        }

        $stmt = Database::connection()->prepare('UPDATE admin_users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE username = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), 'admin']);

        return true;
    }

    private static function user(): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute(['admin']);
        return $stmt->fetch() ?: null;
    }
}

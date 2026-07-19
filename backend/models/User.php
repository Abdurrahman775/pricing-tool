<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class User
{
    public static function create(array $data): int
    {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO users (email, password_hash, name) VALUES (:email, :password_hash, :name)'
        );
        $stmt->execute([
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            'name'          => $data['name'],
        ]);
        return (int) $db->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, email, name, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateProfile(int $id, string $name): void
    {
        $db = getDB();
        $stmt = $db->prepare('UPDATE users SET name = :name WHERE id = :id');
        $stmt->execute(['name' => trim($name), 'id' => $id]);
    }

    public static function updatePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            return false;
        }
        $stmt = $db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([
            'hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            'id'   => $id,
        ]);
        return true;
    }

    public static function authenticate(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if ($user === null) {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }
        // Check if rehash is needed
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST])) {
            $db = getDB();
            $stmt = $db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $stmt->execute([
                'hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
                'id'   => $user['id'],
            ]);
        }
        return $user;
    }
}

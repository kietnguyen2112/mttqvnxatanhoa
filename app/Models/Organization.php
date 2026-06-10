<?php

namespace App\Models;

class Organization
{
    public static function all(): array
    {
        $organizations = Database::connection()
            ->query('SELECT * FROM organizations ORDER BY sort_order ASC, id ASC')
            ->fetchAll();

        foreach ($organizations as &$organization) {
            $organization['leaders'] = self::leaders((int)$organization['id']);
            $organization['chapters'] = self::chapters((int)$organization['id']);
            $organization['hamlets'] = self::hamlets((int)$organization['id']);
        }

        return $organizations;
    }

    public static function memberOrganizations(): array
    {
        $organizations = Database::connection()
            ->query("SELECT * FROM organizations WHERE slug <> 'mttq-viet-nam-xa-tan-hoa' ORDER BY sort_order ASC, id ASC")
            ->fetchAll();

        foreach ($organizations as &$organization) {
            $organization['leaders'] = self::leaders((int)$organization['id']);
            $organization['chapters'] = self::chapters((int)$organization['id']);
            $organization['hamlets'] = self::hamlets((int)$organization['id']);
        }

        return $organizations;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM organizations WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $organization = $stmt->fetch();

        if (!$organization) {
            return null;
        }

        $organization['leaders'] = self::leaders((int)$organization['id']);
        $organization['chapters'] = self::chapters((int)$organization['id']);
        $organization['hamlets'] = self::hamlets((int)$organization['id']);

        return $organization;
    }

    public static function leaders(int $organizationId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM organization_leaders WHERE organization_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$organizationId]);
        return self::normalizeLeaderAvatars($stmt->fetchAll());
    }

    public static function allLeaders(): array
    {
        $leaders = Database::connection()
            ->query('SELECT l.*, o.name AS organization_name, o.short_name AS organization_short_name FROM organization_leaders l JOIN organizations o ON o.id = l.organization_id ORDER BY o.sort_order ASC, l.sort_order ASC, l.id ASC')
            ->fetchAll();

        return self::normalizeLeaderAvatars($leaders);
    }

    public static function findLeader(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM organization_leaders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $leader = $stmt->fetch();

        return $leader ? self::normalizeLeaderAvatar($leader) : null;
    }

    public static function hamlets(int $organizationId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM hamlet_members WHERE organization_id = ? ORDER BY hamlet_name ASC, sort_order ASC, id ASC');
        $stmt->execute([$organizationId]);
        $members = $stmt->fetchAll();
        $hamlets = [];

        foreach ($members as $member) {
            $hamlets[$member['hamlet_name']][] = $member;
        }

        return $hamlets;
    }

    public static function allHamletMembers(): array
    {
        return Database::connection()
            ->query('SELECT m.*, o.name AS organization_name, o.short_name AS organization_short_name FROM hamlet_members m JOIN organizations o ON o.id = m.organization_id ORDER BY m.hamlet_name ASC, o.sort_order ASC, m.sort_order ASC, m.id ASC')
            ->fetchAll();
    }

    public static function chapters(int $organizationId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM organization_chapters WHERE organization_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$organizationId]);
        return $stmt->fetchAll();
    }

    public static function findHamletMember(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM hamlet_members WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        return $member ?: null;
    }

    public static function createLeader(array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO organization_leaders (organization_id, full_name, position, phone, email, avatar, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)$data['organization_id'],
            trim($data['full_name']),
            trim($data['position']),
            trim($data['phone'] ?? ''),
            trim($data['email'] ?? ''),
            trim($data['avatar'] ?? ''),
            (int)($data['sort_order'] ?? 0),
        ]);
    }

    public static function updateLeader(int $id, array $data): void
    {
        $sql = 'UPDATE organization_leaders SET organization_id = ?, full_name = ?, position = ?, phone = ?, email = ?, sort_order = ?';
        $params = [
            (int)$data['organization_id'],
            trim($data['full_name']),
            trim($data['position']),
            trim($data['phone'] ?? ''),
            trim($data['email'] ?? ''),
            (int)($data['sort_order'] ?? 0),
        ];

        if (array_key_exists('avatar', $data)) {
            $sql .= ', avatar = ?';
            $params[] = trim($data['avatar'] ?? '');
        }

        $sql .= ' WHERE id = ?';
        $params[] = $id;

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
    }

    public static function deleteLeader(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM organization_leaders WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function createHamletMember(array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO hamlet_members (organization_id, hamlet_name, full_name, birth_date, role, phone, note, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)$data['organization_id'],
            trim($data['hamlet_name']),
            trim($data['full_name']),
            self::dateOrNull($data['birth_date'] ?? ''),
            trim($data['role']),
            trim($data['phone'] ?? ''),
            trim($data['note'] ?? ''),
            (int)($data['sort_order'] ?? 0),
        ]);
    }

    public static function updateHamletMember(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE hamlet_members SET organization_id = ?, hamlet_name = ?, full_name = ?, birth_date = ?, role = ?, phone = ?, note = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([
            (int)$data['organization_id'],
            trim($data['hamlet_name']),
            trim($data['full_name']),
            self::dateOrNull($data['birth_date'] ?? ''),
            trim($data['role']),
            trim($data['phone'] ?? ''),
            trim($data['note'] ?? ''),
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    public static function deleteHamletMember(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM hamlet_members WHERE id = ?');
        $stmt->execute([$id]);
    }

    private static function normalizeLeaderAvatars(array $leaders): array
    {
        return array_map([self::class, 'normalizeLeaderAvatar'], $leaders);
    }

    private static function normalizeLeaderAvatar(array $leader): array
    {
        $legacyAssets = [
            'img/Hội phụ nữ.jpg' => 'img/hoi-phu-nu.png',
            'img/Hội phụ nữ.jpg' => 'img/hoi-phu-nu.png',
            'img/cụ chiến bịnh.jpg' => 'img/cuu-chien-binh.jpg',
            'img/cụ chiến bịnh.jpg' => 'img/cuu-chien-binh.jpg',
            'img/hội nông dân.jpg' => 'img/hoi-nong-dan.jpg',
            'img/hội nông dân.jpg' => 'img/hoi-nong-dan.jpg',
        ];

        if (isset($legacyAssets[$leader['avatar'] ?? ''])) {
            $leader['avatar'] = $legacyAssets[$leader['avatar']];
        }

        return $leader;
    }

    private static function dateOrNull(string $date): ?string
    {
        $date = trim($date);
        return $date !== '' ? $date : null;
    }

}

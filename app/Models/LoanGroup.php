<?php

namespace App\Models;

class LoanGroup
{
    public static function all(): array
    {
        $groups = Database::connection()
            ->query('SELECT lg.*, o.name AS organization_name, o.short_name AS organization_short_name FROM loan_groups lg JOIN organizations o ON o.id = lg.organization_id ORDER BY lg.hamlet_name ASC, lg.name ASC')
            ->fetchAll();

        foreach ($groups as &$group) {
            $group['members'] = self::members((int)$group['id']);
        }

        return $groups;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT lg.*, o.name AS organization_name, o.short_name AS organization_short_name FROM loan_groups lg JOIN organizations o ON o.id = lg.organization_id WHERE lg.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $group = $stmt->fetch();

        if (!$group) {
            return null;
        }

        $group['members'] = self::members($id);
        return $group;
    }

    public static function members(int $groupId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM loan_group_members WHERE loan_group_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    public static function allMembers(): array
    {
        return Database::connection()
            ->query('SELECT m.*, lg.name AS loan_group_name, lg.hamlet_name, o.short_name AS organization_short_name FROM loan_group_members m JOIN loan_groups lg ON lg.id = m.loan_group_id JOIN organizations o ON o.id = lg.organization_id ORDER BY lg.hamlet_name ASC, lg.name ASC, m.sort_order ASC, m.id ASC')
            ->fetchAll();
    }

    public static function findMember(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM loan_group_members WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        return $member ?: null;
    }

    public static function create(array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO loan_groups (organization_id, hamlet_name, name, leader_name, leader_phone, customer_count, fund_source, outstanding_amount, savings_amount, overdue_amount, rating, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)$data['organization_id'],
            trim($data['hamlet_name']),
            trim($data['name']),
            trim($data['leader_name']),
            trim($data['leader_phone'] ?? ''),
            (int)($data['customer_count'] ?? 0),
            trim($data['fund_source'] ?? ''),
            (float)($data['outstanding_amount'] ?? 0),
            (float)($data['savings_amount'] ?? 0),
            (float)($data['overdue_amount'] ?? 0),
            trim($data['rating'] ?? ''),
            trim($data['note'] ?? ''),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE loan_groups SET organization_id = ?, hamlet_name = ?, name = ?, leader_name = ?, leader_phone = ?, customer_count = ?, fund_source = ?, outstanding_amount = ?, savings_amount = ?, overdue_amount = ?, rating = ?, note = ? WHERE id = ?');
        $stmt->execute([
            (int)$data['organization_id'],
            trim($data['hamlet_name']),
            trim($data['name']),
            trim($data['leader_name']),
            trim($data['leader_phone'] ?? ''),
            (int)($data['customer_count'] ?? 0),
            trim($data['fund_source'] ?? ''),
            (float)($data['outstanding_amount'] ?? 0),
            (float)($data['savings_amount'] ?? 0),
            (float)($data['overdue_amount'] ?? 0),
            trim($data['rating'] ?? ''),
            trim($data['note'] ?? ''),
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM loan_groups WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function createMember(array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO loan_group_members (loan_group_id, full_name, role, phone, loan_amount, outstanding_amount, overdue_amount, purpose, note, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)$data['loan_group_id'],
            trim($data['full_name']),
            trim($data['role'] ?? 'Thành viên'),
            trim($data['phone'] ?? ''),
            (float)($data['loan_amount'] ?? 0),
            (float)($data['outstanding_amount'] ?? ($data['loan_amount'] ?? 0)),
            (float)($data['overdue_amount'] ?? 0),
            trim($data['purpose'] ?? ''),
            trim($data['note'] ?? ''),
            (int)($data['sort_order'] ?? 0),
        ]);
    }

    public static function updateMember(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare('UPDATE loan_group_members SET loan_group_id = ?, full_name = ?, role = ?, phone = ?, loan_amount = ?, outstanding_amount = ?, overdue_amount = ?, purpose = ?, note = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([
            (int)$data['loan_group_id'],
            trim($data['full_name']),
            trim($data['role'] ?? 'Thành viên'),
            trim($data['phone'] ?? ''),
            (float)($data['loan_amount'] ?? 0),
            (float)($data['outstanding_amount'] ?? ($data['loan_amount'] ?? 0)),
            (float)($data['overdue_amount'] ?? 0),
            trim($data['purpose'] ?? ''),
            trim($data['note'] ?? ''),
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    public static function deleteMember(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM loan_group_members WHERE id = ?');
        $stmt->execute([$id]);
    }
}

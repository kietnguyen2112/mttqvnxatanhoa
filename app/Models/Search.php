<?php

namespace App\Models;

class Search
{
    public static function results(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $term = '%' . $query . '%';

        return [
            'posts' => function_exists('post_module_enabled') && post_module_enabled() ? self::posts($term) : [],
            'organizations' => self::organizations($term),
            'leaders' => self::leaders($term),
            'hamletMembers' => self::hamletMembers($term),
            'loanGroups' => self::loanGroups($term),
            'loanMembers' => self::loanMembers($term),
        ];
    }

    public static function total(array $results): int
    {
        $total = 0;

        foreach ($results as $group) {
            $total += count($group);
        }

        return $total;
    }

    private static function organizations(string $term): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM organizations
             WHERE name LIKE ? OR short_name LIKE ? OR description LIKE ?
             ORDER BY sort_order ASC, id ASC
             LIMIT 500'
        );
        $stmt->execute([$term, $term, $term]);

        return $stmt->fetchAll();
    }

    private static function posts(string $term): array
    {
        Post::count();

        $stmt = Database::connection()->prepare(
            "SELECT * FROM posts
             WHERE status = 'published' AND published_at <= ? AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
             ORDER BY published_at DESC, created_at DESC, id DESC
             LIMIT 500"
        );
        $stmt->execute([date('Y-m-d H:i:s'), $term, $term, $term]);

        return $stmt->fetchAll();
    }

    private static function leaders(string $term): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT l.*, o.name AS organization_name, o.short_name AS organization_short_name, o.slug AS organization_slug
             FROM organization_leaders l
             JOIN organizations o ON o.id = l.organization_id
             WHERE l.full_name LIKE ? OR l.position LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR o.name LIKE ? OR o.short_name LIKE ?
             ORDER BY o.sort_order ASC, l.sort_order ASC, l.id ASC
             LIMIT 500'
        );
        $stmt->execute([$term, $term, $term, $term, $term, $term]);

        return $stmt->fetchAll();
    }

    private static function hamletMembers(string $term): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT m.*, o.name AS organization_name, o.short_name AS organization_short_name, o.slug AS organization_slug
             FROM hamlet_members m
             JOIN organizations o ON o.id = m.organization_id
             WHERE m.full_name LIKE ? OR m.role LIKE ? OR m.hamlet_name LIKE ? OR m.phone LIKE ? OR m.note LIKE ? OR o.name LIKE ? OR o.short_name LIKE ?
             ORDER BY m.hamlet_name ASC, o.sort_order ASC, m.sort_order ASC, m.id ASC
             LIMIT 500'
        );
        $stmt->execute([$term, $term, $term, $term, $term, $term, $term]);

        return $stmt->fetchAll();
    }

    private static function loanGroups(string $term): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT lg.*, o.name AS organization_name, o.short_name AS organization_short_name
             FROM loan_groups lg
             JOIN organizations o ON o.id = lg.organization_id
             WHERE lg.name LIKE ? OR lg.hamlet_name LIKE ? OR lg.leader_name LIKE ? OR lg.leader_phone LIKE ? OR lg.fund_source LIKE ? OR lg.note LIKE ? OR o.name LIKE ? OR o.short_name LIKE ?
             ORDER BY lg.hamlet_name ASC, lg.name ASC
             LIMIT 500'
        );
        $stmt->execute([$term, $term, $term, $term, $term, $term, $term, $term]);

        return $stmt->fetchAll();
    }

    private static function loanMembers(string $term): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT m.*, lg.name AS loan_group_name, lg.hamlet_name, o.short_name AS organization_short_name
             FROM loan_group_members m
             JOIN loan_groups lg ON lg.id = m.loan_group_id
             JOIN organizations o ON o.id = lg.organization_id
             WHERE m.full_name LIKE ? OR m.role LIKE ? OR m.phone LIKE ? OR m.purpose LIKE ? OR m.note LIKE ? OR lg.name LIKE ? OR lg.hamlet_name LIKE ? OR o.short_name LIKE ?
             ORDER BY lg.hamlet_name ASC, lg.name ASC, m.sort_order ASC, m.id ASC
             LIMIT 500'
        );
        $stmt->execute([$term, $term, $term, $term, $term, $term, $term, $term]);

        return $stmt->fetchAll();
    }
}

<?php

namespace App\Controllers;

use App\Models\Search;

class SearchController extends BaseController
{
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $results = Search::results($query);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        [$results, $total, $totalPages] = $this->paginateResults($results, $page, $perPage);

        $this->view('search/index', [
            'title' => 'Tìm kiếm',
            'metaDescription' => 'Tìm kiếm bài đăng, thông tin tổ chức, cán bộ, hồ sơ cấp ấp và tổ vay vốn trên cổng thông tin MTTQ Việt Nam xã Tân Hòa.',
            'query' => $query,
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ]);
    }

    private function paginateResults(array $results, int $page, int $perPage): array
    {
        $orderedTypes = post_module_enabled()
            ? ['posts', 'organizations', 'leaders', 'hamletMembers', 'loanGroups', 'loanMembers']
            : ['organizations', 'leaders', 'hamletMembers', 'loanGroups', 'loanMembers'];
        $flat = [];
        foreach ($orderedTypes as $type) {
            foreach (($results[$type] ?? []) as $item) {
                $flat[] = ['type' => $type, 'item' => $item];
            }
        }

        $total = count($flat);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $slice = array_slice($flat, ($page - 1) * $perPage, $perPage);

        $paginated = array_fill_keys($orderedTypes, []);
        foreach ($slice as $row) {
            $paginated[$row['type']][] = $row['item'];
        }

        return [$paginated, $total, $totalPages];
    }
}

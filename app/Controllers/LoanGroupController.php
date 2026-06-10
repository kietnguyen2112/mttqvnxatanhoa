<?php

namespace App\Controllers;

use App\Models\LoanGroup;
use App\Models\Organization;

class LoanGroupController extends BaseController
{
    public function index(): void
    {
        $organizations = Organization::memberOrganizations();
        $selectedOrganizationId = (int)($_GET['organization_id'] ?? 0);
        $selectedOrganization = null;
        $loanGroups = [];
        $groupCounts = [];
        $loanProgramStatsByOrganizationId = [];
        $groups = LoanGroup::all();
        $overviewStats = [
            'organization_count' => count($organizations),
            'group_count' => 0,
            'member_count' => 0,
            'outstanding_amount' => 0.0,
            'savings_amount' => 0.0,
            'overdue_amount' => 0.0,
        ];

        foreach ($groups as $group) {
            $organizationId = (int)$group['organization_id'];
            $groupCounts[$organizationId] = ($groupCounts[$organizationId] ?? 0) + 1;
            $overviewStats['group_count']++;
            $loanProgramStatsByOrganizationId[$organizationId] = $loanProgramStatsByOrganizationId[$organizationId] ?? [
                'tong_to' => 0,
                'thanh_vien' => 0,
                'to_tot' => 0,
                'to_kha' => 0,
                'to_trung_binh' => 0,
                'to_yeu' => 0,
                'to_no_qua_han' => 0,
                'du_no' => 0.0,
                'tien_gui' => 0.0,
                'no_qua_han_amount' => 0.0,
            ];

            $loanProgramStatsByOrganizationId[$organizationId]['tong_to']++;

            $note = mb_strtolower((string)($group['note'] ?? ''), 'UTF-8');
            $rating = mb_strtolower((string)($group['rating'] ?? ''), 'UTF-8');
            if ($rating === '' && preg_match('/xếp loại:\s*([^;]+)/iu', (string)($group['note'] ?? ''), $match)) {
                $rating = mb_strtolower(trim($match[1]), 'UTF-8');
            }
            if ($rating === 'tốt' || str_contains($note, 'xếp loại: tốt')) {
                $loanProgramStatsByOrganizationId[$organizationId]['to_tot']++;
            } elseif ($rating === 'khá' || str_contains($note, 'xếp loại: khá')) {
                $loanProgramStatsByOrganizationId[$organizationId]['to_kha']++;
            } elseif ($rating === 'trung bình' || str_contains($note, 'xếp loại: trung bình')) {
                $loanProgramStatsByOrganizationId[$organizationId]['to_trung_binh']++;
            } elseif ($rating === 'yếu' || str_contains($note, 'xếp loại: yếu')) {
                $loanProgramStatsByOrganizationId[$organizationId]['to_yeu']++;
            }

            $groupOverdueAmount = 0.0;
            if (!empty($group['members'])) {
                foreach (($group['members'] ?? []) as $member) {
                    $outstandingAmount = (float)($member['outstanding_amount'] ?? $member['loan_amount'] ?? 0);
                    $overdueAmount = (float)($member['overdue_amount'] ?? 0);
                    $loanProgramStatsByOrganizationId[$organizationId]['thanh_vien']++;
                    $loanProgramStatsByOrganizationId[$organizationId]['du_no'] += $outstandingAmount;
                    $groupOverdueAmount += (float)($member['overdue_amount'] ?? 0);
                    $overviewStats['member_count']++;
                    $overviewStats['outstanding_amount'] += $outstandingAmount;
                    $overviewStats['overdue_amount'] += $overdueAmount;
                }
            } else {
                $memberCount = (int)($group['customer_count'] ?? $group['member_count'] ?? 0);
                $outstandingAmount = (float)($group['outstanding_amount'] ?? 0);
                $overdueAmount = (float)($group['overdue_amount'] ?? 0);
                $loanProgramStatsByOrganizationId[$organizationId]['thanh_vien'] += $memberCount;
                $loanProgramStatsByOrganizationId[$organizationId]['du_no'] += $outstandingAmount;
                $groupOverdueAmount = $overdueAmount;
                $overviewStats['member_count'] += $memberCount;
                $overviewStats['outstanding_amount'] += $outstandingAmount;
                $overviewStats['overdue_amount'] += $overdueAmount;
            }
            $savingsAmount = (float)($group['savings_amount'] ?? 0);
            $loanProgramStatsByOrganizationId[$organizationId]['tien_gui'] += $savingsAmount;
            $overviewStats['savings_amount'] += $savingsAmount;

            if ($groupOverdueAmount > 0) {
                $loanProgramStatsByOrganizationId[$organizationId]['to_no_qua_han']++;
                $loanProgramStatsByOrganizationId[$organizationId]['no_qua_han_amount'] += $groupOverdueAmount;
            }

        }

        $validOrganizationIds = array_map(static fn (array $organization): int => (int)$organization['id'], $organizations);
        if (!in_array($selectedOrganizationId, $validOrganizationIds, true)) {
            $selectedOrganizationId = 0;
            $highestGroupCount = -1;
            foreach ($organizations as $organization) {
                $organizationId = (int)$organization['id'];
                $organizationGroupCount = (int)($groupCounts[$organizationId] ?? 0);
                if ($organizationGroupCount > $highestGroupCount) {
                    $selectedOrganizationId = $organizationId;
                    $highestGroupCount = $organizationGroupCount;
                }
            }
        }

        foreach ($organizations as $organization) {
            $organizationId = (int)$organization['id'];
            $stats = $loanProgramStatsByOrganizationId[$organizationId] ?? null;
            $loanProgramStatsByOrganizationId[$organizationId] = [
                'tong_to' => $stats ? ((int)$stats['tong_to'] . ' tổ') : '0 tổ',
                'thanh_vien' => $stats ? ((int)$stats['thanh_vien'] . ' người') : '0 người',
                'to_tot' => $stats ? ((int)$stats['to_tot'] . ' tổ') : '0 tổ',
                'to_kha' => $stats ? ((int)$stats['to_kha'] . ' tổ') : '0 tổ',
                'to_trung_binh' => $stats ? ((int)$stats['to_trung_binh'] . ' tổ') : '0 tổ',
                'to_yeu' => $stats ? ((int)$stats['to_yeu'] . ' tổ') : '0 tổ',
                'to_no_qua_han' => $stats ? ((int)$stats['to_no_qua_han'] . ' tổ') : '0 tổ',
                'du_no' => $stats ? number_format((float)$stats['du_no'], 0, ',', '.') . ' đ' : '0 đ',
                'tien_gui' => $stats ? number_format((float)$stats['tien_gui'], 0, ',', '.') . ' đ' : '0 đ',
                'no_qua_han_amount' => $stats ? (float)$stats['no_qua_han_amount'] : 0.0,
                'no_qua_han' => $stats && (float)$stats['no_qua_han_amount'] > 0
                    ? number_format((float)$stats['no_qua_han_amount'], 0, ',', '.') . ' đ'
                    : '',
                'no_qua_han_rate' => $stats && (float)$stats['no_qua_han_amount'] > 0 && (float)$stats['du_no'] > 0
                    ? number_format(((float)$stats['no_qua_han_amount'] / (float)$stats['du_no']) * 100, 2, ',', '.') . '%'
                    : '',
            ];

            if ((int)$organization['id'] === $selectedOrganizationId) {
                $selectedOrganization = $organization;
            }
        }

        if ($selectedOrganization) {
            $loanGroups = array_values(array_filter(
                $groups,
                static fn (array $group): bool => (int)$group['organization_id'] === $selectedOrganizationId
            ));
        }

        $this->view('loan-groups/index', [
            'title' => 'Tổ vay vốn',
            'metaDescription' => 'Tra cứu danh sách tổ vay vốn, thành viên, dư nợ và tình hình nợ quá hạn do các tổ chức thành viên quản lý tại xã Tân Hòa.',
            'organizations' => $organizations,
            'selectedOrganization' => $selectedOrganization,
            'selectedOrganizationId' => $selectedOrganization ? $selectedOrganizationId : 0,
            'loanGroups' => $selectedOrganization ? $loanGroups : [],
            'groupCounts' => $groupCounts,
            'loanProgramStatsByOrganizationId' => $loanProgramStatsByOrganizationId,
            'overviewStats' => $overviewStats,
        ]);
    }

    public function show(): void
    {
        $loanGroup = LoanGroup::find((int)($_GET['id'] ?? 0));
        if (!$loanGroup) {
            http_response_code(404);
        }

        $this->view('loan-groups/show', [
            'title' => $loanGroup['name'] ?? 'Không tìm thấy tổ vay vốn',
            'metaDescription' => $loanGroup
                ? 'Chi tiết ' . $loanGroup['name'] . ', tổ trưởng, thành viên và số liệu vay vốn tại xã Tân Hòa.'
                : 'Không tìm thấy thông tin tổ vay vốn tại xã Tân Hòa.',
            'loanGroup' => $loanGroup,
        ]);
    }
}

<?php

namespace App\Controllers;

use App\Models\Organization;

class OrganizationController extends BaseController
{
    private const MEMBER_ORGANIZATION_SLUGS = [
        'doan-thanh-nien',
        'hoi-lien-hiep-phu-nu',
        'hoi-cuu-chien-binh',
        'hoi-nong-dan',
    ];

    public function index(): void
    {
        $organizations = Organization::all();
        $featuredOrganization = null;
        $memberOrganizations = [];

        foreach ($organizations as $organization) {
            if (($organization['slug'] ?? '') === 'mttq-viet-nam-xa-tan-hoa') {
                $featuredOrganization = $organization;
                continue;
            }

            $chapters = $this->chaptersForOrganization($organization);
            $profile = $this->profileForOrganization($organization, $chapters);
            $hasStatisticalData = !empty($organization['chapters']);

            $memberOrganizations[] = [
                'organization' => $organization,
                'profile' => $profile,
                'headLeader' => $organization['leaders'][0] ?? null,
                'leaderCount' => count($organization['leaders'] ?? []),
                'unitCount' => count($chapters),
                'memberCount' => $this->chapterMemberTotal($chapters),
                'unitMetricLabel' => $hasStatisticalData
                    ? $profile['unitTitle']
                    : $profile['unitTitle'] . ' có hồ sơ',
                'memberMetricLabel' => $hasStatisticalData
                    ? $profile['memberTitle']
                    : 'Hồ sơ chi tiết',
                'dataStatus' => $hasStatisticalData
                    ? 'Số liệu thống kê đã cập nhật'
                    : 'Số hồ sơ chi tiết hiện có',
            ];
        }

        $this->view('orgs/index', [
            'title' => 'Tổ chức thành viên',
            'metaDescription' => 'Danh sách tổ chức thành viên, cán bộ phụ trách, chi đoàn, chi hội và hồ sơ hoạt động của MTTQ Việt Nam xã Tân Hòa.',
            'featuredOrganization' => $featuredOrganization,
            'memberOrganizations' => $memberOrganizations,
        ]);
    }

    public function show(): void
    {
        $organization = Organization::findBySlug($_GET['slug'] ?? '');
        if (!$organization) {
            http_response_code(404);
        }

        $chapters = $organization ? $this->chaptersForOrganization($organization) : [];
        $profile = $organization ? $this->profileForOrganization($organization, $chapters) : [];

        $this->view('orgs/show', [
            'title' => $organization['name'] ?? 'Không tìm thấy tổ chức',
            'metaDescription' => $organization
                ? 'Thông tin ' . $organization['name'] . ', cán bộ phụ trách và hồ sơ tổ chức thành viên tại xã Tân Hòa.'
                : 'Không tìm thấy thông tin tổ chức thành viên tại xã Tân Hòa.',
            'organization' => $organization,
            'memberOrganizations' => $organization && ($organization['slug'] ?? '') === 'mttq-viet-nam-xa-tan-hoa'
                ? Organization::memberOrganizations()
                : [],
            'memberOrganizationProfile' => $profile,
            'organizationChapters' => $chapters,
            'organizationMemberTotal' => $this->chapterMemberTotal($chapters),
        ]);
    }

    public function chapter(): void
    {
        $organization = Organization::findBySlug($_GET['slug'] ?? '');
        $chapters = $organization ? $this->chaptersForOrganization($organization) : [];
        $chapterIndex = filter_var($_GET['chapter'] ?? null, FILTER_VALIDATE_INT);
        $chapter = $chapterIndex !== false && $chapterIndex !== null
            ? ($chapters[$chapterIndex] ?? null)
            : null;

        if (!$organization || !$this->isMemberOrganization($organization) || !$chapter) {
            http_response_code(404);
        }

        $profile = $organization ? $this->profileForOrganization($organization, $chapters) : [];
        $members = $organization && $chapter
            ? $this->membersForChapter($organization['hamlets'] ?? [], $chapter[0])
            : [];

        $this->view('orgs/chapter-show', [
            'title' => $chapter ? $chapter[0] : 'Không tìm thấy chi hội',
            'metaDescription' => $chapter
                ? 'Danh sách thành viên ' . $chapter[0] . ' thuộc tổ chức thành viên MTTQ Việt Nam xã Tân Hòa.'
                : 'Không tìm thấy thông tin chi đoàn, chi hội tại xã Tân Hòa.',
            'organization' => $organization,
            'chapter' => $chapter,
            'members' => $members,
            'profile' => $profile,
        ]);
    }

    private function isMemberOrganization(array $organization): bool
    {
        return in_array((string)($organization['slug'] ?? ''), self::MEMBER_ORGANIZATION_SLUGS, true);
    }

    private function chaptersForOrganization(array $organization): array
    {
        if (!$this->isMemberOrganization($organization)) {
            return [];
        }

        if (!empty($organization['chapters'])) {
            return array_map(static fn (array $chapter): array => [
                (string)$chapter['name'],
                (int)$chapter['member_count'],
                (string)($chapter['note'] ?? ''),
                (int)($chapter['household_count'] ?? 0),
                (int)($chapter['male_count'] ?? 0),
                (int)($chapter['female_count'] ?? 0),
            ], $organization['chapters']);
        }

        $chapters = [];
        foreach (($organization['hamlets'] ?? []) as $chapterName => $members) {
            $chapters[] = [(string)$chapterName, count($members), '', 0, 0, 0];
        }

        return $chapters;
    }

    private function chapterMemberTotal(array $chapters): int
    {
        return array_sum(array_map(static fn (array $chapter): int => (int)($chapter[1] ?? 0), $chapters));
    }

    private function profileForOrganization(array $organization, array $chapters): array
    {
        $slug = (string)($organization['slug'] ?? '');
        $isYouthUnion = $slug === 'doan-thanh-nien';
        $unitLabel = $isYouthUnion ? 'chi đoàn' : 'chi hội';
        $unitTitle = $isYouthUnion ? 'Chi đoàn' : 'Chi hội';
        $memberLabel = $isYouthUnion ? 'đoàn viên' : 'hội viên';
        $memberTitle = $isYouthUnion ? 'Đoàn viên' : 'Hội viên';

        return [
            'enabled' => $this->isMemberOrganization($organization),
            'unitLabel' => $unitLabel,
            'unitTitle' => $unitTitle,
            'memberLabel' => $memberLabel,
            'memberTitle' => $memberTitle,
            'statsHeading' => 'Thống kê đơn vị',
            'stats' => [
                ['label' => 'Cán bộ cấp xã', 'value' => (string)count($organization['leaders'] ?? [])],
                ['label' => $unitTitle . ' đã cập nhật', 'value' => (string)count($chapters)],
                ['label' => $memberTitle . ' đã cập nhật', 'value' => (string)$this->chapterMemberTotal($chapters)],
            ],
            'structureNote' => 'Số liệu được tổng hợp từ danh sách thành viên chi đoàn, chi hội đã cập nhật trong hệ thống.',
        ];
    }

    private function membersForChapter(array $hamlets, string $chapterName): array
    {
        $chapterKey = mb_strtolower(trim($chapterName), 'UTF-8');

        foreach ($hamlets as $hamletName => $members) {
            if (mb_strtolower(trim((string)$hamletName), 'UTF-8') === $chapterKey) {
                return $members;
            }
        }

        return [];
    }
}

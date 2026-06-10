<?php

namespace App\Controllers;

use App\Models\Organization;
use App\Models\Post;

class AboutController extends BaseController
{
    public function index(): void
    {
        $this->view('about/index', [
            'title' => 'Giới thiệu MTTQVN xã',
            'metaDescription' => 'Giới thiệu Ủy ban MTTQ Việt Nam xã Tân Hòa, Ban Thường trực, tổ chức thành viên và thông tin tổng hợp tại địa phương.',
            'mttq' => Organization::findBySlug('mttq-viet-nam-xa-tan-hoa'),
            'memberOrganizations' => Organization::memberOrganizations(),
            'latestPosts' => post_module_enabled() ? Post::latest(20) : [],
        ]);
    }
}

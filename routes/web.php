<?php

use App\Controllers\AboutController;
use App\Controllers\AdminController;
use App\Controllers\DocumentController;
use App\Controllers\LoanGroupController;
use App\Controllers\OrganizationController;
use App\Controllers\PostController;
use App\Controllers\SearchController;

return [
    'GET' => [
        '/' => [AboutController::class, 'index'],
        '/about' => [AboutController::class, 'index'],
        '/organizations' => [OrganizationController::class, 'index'],
        '/organizations/show' => [OrganizationController::class, 'show'],
        '/organizations/chapter' => [OrganizationController::class, 'chapter'],
        '/loan-groups' => [LoanGroupController::class, 'index'],
        '/loan-groups/show' => [LoanGroupController::class, 'show'],
        '/documents' => [DocumentController::class, 'index'],
        '/documents/preview' => [DocumentController::class, 'preview'],
        '/documents/download' => [DocumentController::class, 'download'],
        '/posts' => [PostController::class, 'index'],
        '/posts/show' => [PostController::class, 'show'],
        '/search' => [SearchController::class, 'index'],
        '/admin' => [AdminController::class, 'dashboard'],
        '/admin/posts' => [AdminController::class, 'posts'],
        '/admin/posts/preview' => [AdminController::class, 'previewPost'],
        '/admin/leaders' => [AdminController::class, 'leaders'],
        '/admin/hamlet-members' => [AdminController::class, 'hamletMembers'],
        '/admin/loan-groups' => [AdminController::class, 'loanGroups'],
        '/admin/loan-members' => [AdminController::class, 'loanMembers'],
        '/admin/documents' => [AdminController::class, 'documents'],
        '/admin/search' => [AdminController::class, 'search'],
        '/admin/password' => [AdminController::class, 'password'],
        '/admin/export' => [AdminController::class, 'exportExcel'],
        '/admin/import' => [AdminController::class, 'import'],
        '/admin/logout' => [AdminController::class, 'logout'],
    ],
    'POST' => [
        '/admin/login' => [AdminController::class, 'login'],
        '/admin/logout' => [AdminController::class, 'logout'],
        '/admin/posts' => [AdminController::class, 'storePost'],
        '/admin/posts/update' => [AdminController::class, 'updatePost'],
        '/admin/posts/content-image' => [AdminController::class, 'uploadPostContentImage'],
        '/admin/posts/status' => [AdminController::class, 'updatePostStatus'],
        '/admin/posts/featured' => [AdminController::class, 'togglePostFeatured'],
        '/admin/posts/delete' => [AdminController::class, 'deletePost'],
        '/admin/organization-leaders' => [AdminController::class, 'storeOrganizationLeader'],
        '/admin/organization-leaders/update' => [AdminController::class, 'updateOrganizationLeader'],
        '/admin/organization-leaders/delete' => [AdminController::class, 'deleteOrganizationLeader'],
        '/admin/hamlet-members' => [AdminController::class, 'storeHamletMember'],
        '/admin/hamlet-members/update' => [AdminController::class, 'updateHamletMember'],
        '/admin/hamlet-members/delete' => [AdminController::class, 'deleteHamletMember'],
        '/admin/loan-groups' => [AdminController::class, 'storeLoanGroup'],
        '/admin/loan-groups/update' => [AdminController::class, 'updateLoanGroup'],
        '/admin/loan-groups/delete' => [AdminController::class, 'deleteLoanGroup'],
        '/admin/loan-members' => [AdminController::class, 'storeLoanMember'],
        '/admin/loan-members/update' => [AdminController::class, 'updateLoanMember'],
        '/admin/loan-members/delete' => [AdminController::class, 'deleteLoanMember'],
        '/admin/documents' => [AdminController::class, 'storeDocument'],
        '/admin/documents/update' => [AdminController::class, 'updateDocument'],
        '/admin/documents/delete' => [AdminController::class, 'deleteDocument'],
        '/admin/password' => [AdminController::class, 'changePassword'],
        '/admin/import' => [AdminController::class, 'importExcel'],
    ],
];

<?php

namespace App\Controllers;

use App\Models\AdminUser;
use App\Models\Document;
use App\Models\ExcelImporter;
use App\Models\LoanGroup;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Search;

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        if (!$this->isAuthenticated()) {
            $this->refreshLoginCaptcha();
            $this->view('admin/login', [
                'title' => 'Đăng nhập quản trị',
                'error' => $_GET['error'] ?? '',
                'captchaQuestion' => $_SESSION['admin_login_captcha_question'] ?? '',
            ], 'admin');
            return;
        }

        $this->renderAdmin('admin/dashboard', 'Tổng quan quản trị', [
            'organizations' => Organization::all(),
            'leaders' => Organization::allLeaders(),
            'hamletMembers' => Organization::allHamletMembers(),
            'loanGroups' => LoanGroup::all(),
            'loanMembers' => LoanGroup::allMembers(),
            'documentCount' => Document::count(),
            'postCount' => post_module_enabled() ? Post::count() : null,
        ]);
    }

    public function posts(): void
    {
        $editPost = isset($_GET['edit']) ? Post::find((int)$_GET['edit']) : null;
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'status' => trim((string)($_GET['status'] ?? '')),
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pagination = Post::paginated($page, 10, $filters);
        $postForm = $this->consumePostOldInput();
        if ($editPost) {
            $postForm = $postForm ?: $editPost;
        }

        $this->renderAdmin('admin/posts', 'Quản lý bài đăng', [
            'posts' => $pagination['items'],
            'total' => $pagination['total'],
            'page' => $pagination['page'],
            'totalPages' => $pagination['totalPages'],
            'filters' => $filters,
            'editPost' => $editPost,
            'postForm' => $postForm,
            'postStatus' => $this->consumePostStatus(),
            'postErrors' => $this->consumePostErrors(),
        ]);
    }

    public function previewPost(): void
    {
        $this->requireAuth();

        $post = Post::find((int)($_GET['id'] ?? 0));
        if (!$post) {
            http_response_code(404);
        }

        $description = $post ? (string)($post['meta_description'] ?: $post['excerpt'] ?: $post['title']) : 'Không tìm thấy bài đăng.';
        $this->view('posts/show', [
            'title' => $post ? '[Xem trước] ' . (string)($post['meta_title'] ?: $post['title']) : 'Không tìm thấy bài đăng',
            'metaDescription' => $description,
            'post' => $post,
            'isPreview' => true,
            'og' => [],
        ]);
    }

    public function leaders(): void
    {
        $editLeader = isset($_GET['edit']) ? Organization::findLeader((int)$_GET['edit']) : null;

        $this->renderAdmin('admin/leaders', 'Quản lý cán bộ', [
            'organizations' => Organization::all(),
            'leaders' => Organization::allLeaders(),
            'editLeader' => $editLeader,
            'importStatus' => $this->consumeImportStatus(),
        ]);
    }

    public function hamletMembers(): void
    {
        $editMember = isset($_GET['edit']) ? Organization::findHamletMember((int)$_GET['edit']) : null;

        $this->renderAdmin('admin/hamlet-members', 'Thành viên và Ban Công tác Mặt trận ấp', [
            'memberOrganizations' => Organization::all(),
            'hamletMembers' => Organization::allHamletMembers(),
            'editMember' => $editMember,
            'importStatus' => $this->consumeImportStatus(),
        ]);
    }

    public function loanGroups(): void
    {
        $editGroup = isset($_GET['edit']) ? LoanGroup::find((int)$_GET['edit']) : null;

        $this->renderAdmin('admin/loan-groups', 'Tổ vay vốn', [
            'memberOrganizations' => Organization::memberOrganizations(),
            'loanGroups' => LoanGroup::all(),
            'editGroup' => $editGroup,
            'importStatus' => $this->consumeImportStatus(),
        ]);
    }

    public function loanMembers(): void
    {
        $editMember = isset($_GET['edit']) ? LoanGroup::findMember((int)$_GET['edit']) : null;

        $this->renderAdmin('admin/loan-members', 'Thành viên tổ vay vốn', [
            'loanGroups' => LoanGroup::all(),
            'loanMembers' => LoanGroup::allMembers(),
            'editMember' => $editMember,
            'importStatus' => $this->consumeImportStatus(),
        ]);
    }

    public function documents(): void
    {
        $editDocument = isset($_GET['edit']) ? Document::find((int)$_GET['edit']) : null;

        $this->renderAdmin('admin/documents', 'Quản lý văn bản', [
            'documents' => Document::all(),
            'editDocument' => $editDocument,
            'documentStatus' => $this->consumeDocumentStatus(),
        ]);
    }

    public function password(): void
    {
        $this->renderAdmin('admin/password', 'Đổi mật khẩu', [
            'status' => $_GET['status'] ?? '',
            'error' => $_GET['error'] ?? '',
        ]);
    }

    public function import(): void
    {
        $this->renderAdmin('admin/import', 'Nhập dữ liệu Excel', [
            'types' => ExcelImporter::types(),
            'status' => $_SESSION['import_status'] ?? null,
        ]);
        unset($_SESSION['import_status']);
    }

    public function search(): void
    {
        $query = trim($_GET['q'] ?? '');
        $results = Search::results($query);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        [$results, $total, $totalPages] = $this->paginateResults($results, $page, $perPage);

        $this->renderAdmin('admin/search', 'Tìm kiếm quản trị', [
            'query' => $query,
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ]);
    }

    public function exportExcel(): void
    {
        $this->requireAuth();
        $type = $_GET['type'] ?? '';
        $filename = '';
        $rows = [];
        $widths = [];
        $numericColumns = [];
        $title = '';
        $requiredHeaders = [];
        $notes = [];

        if ($type === 'leaders') {
            $title = 'Danh sách cán bộ';
            $filename = 'danh_sach_can_bo.xlsx';
            $requiredHeaders = ['Tổ chức', 'Họ và tên', 'Chức vụ'];
            $rows[] = ['Tổ chức', 'Họ và tên', 'Chức vụ', 'Điện thoại', 'Email', 'Thứ tự'];
            foreach (Organization::allLeaders() as $row) {
                $rows[] = [
                    $row['organization_short_name'],
                    $row['full_name'],
                    $row['position'],
                    $row['phone'],
                    $row['email'],
                    (int)$row['sort_order'],
                ];
            }
            $widths = [24, 28, 34, 18, 30, 12];
            $numericColumns = [5 => 6];
        } elseif ($type === 'hamlet_members') {
            $title = 'Danh sách thành viên và Ban Công tác Mặt trận ấp';
            $filename = 'danh_sach_thanh_vien_chi_doan_chi_hoi.xlsx';
            $requiredHeaders = ['Tổ chức', 'Tên chi đoàn, chi hội / ấp', 'Họ và tên', 'Vai trò'];
            $rows[] = ['Tổ chức', 'Tên chi đoàn, chi hội / ấp', 'Họ và tên', 'Ngày sinh', 'Vai trò', 'Điện thoại', 'Ghi chú', 'Thứ tự'];
            foreach (Organization::allHamletMembers() as $row) {
                $rows[] = [
                    $row['organization_short_name'],
                    $row['hamlet_name'],
                    $row['full_name'],
                    $row['birth_date'] ?? '',
                    $row['role'],
                    $row['phone'],
                    $row['note'],
                    (int)$row['sort_order'],
                ];
            }
            $widths = [24, 30, 28, 16, 34, 18, 34, 12];
            $numericColumns = [7 => 6];
        } elseif ($type === 'loan_groups') {
            $title = 'Danh sách tổ vay vốn';
            $filename = 'danh_sach_to_vay_von.xlsx';
            $requiredHeaders = ['Tổ chức', 'Tên ấp', 'Tên tổ vay vốn', 'Tên tổ trưởng'];
            $rows[] = ['Tổ chức', 'Tên ấp', 'Tên tổ vay vốn', 'Tên tổ trưởng', 'Điện thoại tổ trưởng', 'Số khách hàng', 'Nguồn vốn', 'Dư nợ', 'Tiền gửi', 'Nợ quá hạn', 'Xếp loại tổ', 'Ghi chú'];
            foreach (LoanGroup::all() as $row) {
                $rows[] = [
                    $row['organization_short_name'],
                    $row['hamlet_name'],
                    $row['name'],
                    $row['leader_name'],
                    $row['leader_phone'],
                    (int)($row['customer_count'] ?? 0),
                    $row['fund_source'],
                    (float)($row['outstanding_amount'] ?? 0),
                    (float)($row['savings_amount'] ?? 0),
                    (float)($row['overdue_amount'] ?? 0),
                    $row['rating'] ?? '',
                    $row['note'],
                ];
            }
            $widths = [24, 16, 34, 28, 22, 14, 30, 18, 18, 18, 16, 34];
            $numericColumns = [5 => 6, 7 => 6, 8 => 6, 9 => 6];
        } elseif ($type === 'loan_members') {
            $title = 'Danh sách thành viên tổ vay vốn';
            $filename = 'danh_sach_thanh_vien_to_vay_von.xlsx';
            $requiredHeaders = ['Tổ vay vốn', 'Họ và tên'];
            $rows[] = ['Tổ vay vốn', 'Họ và tên', 'Vai trò', 'Điện thoại', 'Số tiền vay ban đầu', 'Dư nợ', 'Nợ quá hạn', 'Mục đích vay', 'Ghi chú', 'Thứ tự'];
            foreach (LoanGroup::allMembers() as $row) {
                $rows[] = [
                    $row['loan_group_name'],
                    $row['full_name'],
                    $row['role'],
                    $row['phone'],
                    (float)$row['loan_amount'],
                    (float)($row['outstanding_amount'] ?? $row['loan_amount']),
                    (float)($row['overdue_amount'] ?? 0),
                    $row['purpose'],
                    $row['note'],
                    (int)$row['sort_order'],
                ];
            }
            $widths = [34, 28, 18, 18, 18, 18, 18, 28, 34, 12];
            $numericColumns = [4 => 6, 5 => 6, 6 => 6, 9 => 6];
        } else {
            $this->redirect('/admin');
        }

        $notes = array_fill_keys($rows[0], '');
        require_once __DIR__ . '/../../storage/import-templates/create_templates.php';
        $tempPath = tempnam(sys_get_temp_dir(), 'mttq-export-');
        $xlsxPath = $tempPath . '.xlsx';
        @unlink($tempPath);
        createXlsx($xlsxPath, [
            'title' => $title,
            'requiredHeaders' => $requiredHeaders,
            'notes' => $notes,
            'widths' => $widths,
            'numericColumns' => $numericColumns,
            'rows' => $rows,
        ]);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($xlsxPath));
        readfile($xlsxPath);
        @unlink($xlsxPath);
        exit;
    }

    public function login(): void
    {
        if ($this->isLoginRateLimited()) {
            $this->refreshLoginCaptcha();
            $this->redirect('/admin?error=rate-limit');
        }

        $captchaInput = trim((string)($_POST['captcha'] ?? ''));
        $captchaAnswer = (string)($_SESSION['admin_login_captcha_answer'] ?? '');
        if ($captchaAnswer === '' && $captchaInput !== '' && $this->isValidLoginCaptchaSignature($captchaInput)) {
            $captchaAnswer = $captchaInput;
        }

        if ($captchaInput === '' || $captchaAnswer === '' || !hash_equals($captchaAnswer, $captchaInput)) {
            $this->recordFailedLogin();
            $this->refreshLoginCaptcha();
            $this->redirect('/admin?error=captcha');
        }

        $password = $_POST['password'] ?? '';

        if (AdminUser::verifyPassword($password)) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = AdminUser::currentId();
            $this->clearFailedLogins();
            unset($_SESSION['admin_login_captcha_question'], $_SESSION['admin_login_captcha_answer']);
            $this->redirect('/admin');
        }

        $this->recordFailedLogin();
        $this->refreshLoginCaptcha();
        $this->redirect('/admin?error=password');
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/admin');
        }

        $this->requireAuth();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool)($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        $this->redirect('/');
    }

    public function storeOrganizationLeader(): void
    {
        $this->requireAuth();
        $avatarPath = $this->uploadStaffAvatar($_FILES['avatar'] ?? null);
        if ($avatarPath) {
            $_POST['avatar'] = $avatarPath;
        }
        Organization::createLeader($_POST);
        $this->redirect('/admin/leaders?created=leader');
    }

    public function deleteOrganizationLeader(): void
    {
        $this->requireAuth();
        Organization::deleteLeader((int)($_POST['id'] ?? 0));
        $this->redirect('/admin/leaders?deleted=leader');
    }

    public function updateOrganizationLeader(): void
    {
        $this->requireAuth();
        $avatarPath = $this->uploadStaffAvatar($_FILES['avatar'] ?? null, (int)($_POST['id'] ?? 0));
        if ($avatarPath) {
            $_POST['avatar'] = $avatarPath;
        }
        Organization::updateLeader((int)($_POST['id'] ?? 0), $_POST);
        $this->redirect('/admin/leaders?updated=leader');
    }

    public function storeHamletMember(): void
    {
        $this->requireAuth();
        Organization::createHamletMember($_POST);
        $this->redirect('/admin/hamlet-members?created=hamlet-member');
    }

    public function deleteHamletMember(): void
    {
        $this->requireAuth();
        Organization::deleteHamletMember((int)($_POST['id'] ?? 0));
        $this->redirect('/admin/hamlet-members?deleted=hamlet-member');
    }

    public function updateHamletMember(): void
    {
        $this->requireAuth();
        Organization::updateHamletMember((int)($_POST['id'] ?? 0), $_POST);
        $this->redirect('/admin/hamlet-members?updated=hamlet-member');
    }

    public function storeLoanGroup(): void
    {
        $this->requireAuth();
        LoanGroup::create($_POST);
        $this->redirect('/admin/loan-groups?created=loan-group');
    }

    public function deleteLoanGroup(): void
    {
        $this->requireAuth();
        LoanGroup::delete((int)($_POST['id'] ?? 0));
        $this->redirect('/admin/loan-groups?deleted=loan-group');
    }

    public function updateLoanGroup(): void
    {
        $this->requireAuth();
        LoanGroup::update((int)($_POST['id'] ?? 0), $_POST);
        $this->redirect('/admin/loan-groups?updated=loan-group');
    }

    public function storeLoanMember(): void
    {
        $this->requireAuth();
        LoanGroup::createMember($_POST);
        $this->redirect('/admin/loan-members?created=loan-member');
    }

    public function deleteLoanMember(): void
    {
        $this->requireAuth();
        LoanGroup::deleteMember((int)($_POST['id'] ?? 0));
        $this->redirect('/admin/loan-members?deleted=loan-member');
    }

    public function updateLoanMember(): void
    {
        $this->requireAuth();
        LoanGroup::updateMember((int)($_POST['id'] ?? 0), $_POST);
        $this->redirect('/admin/loan-members?updated=loan-member');
    }

    public function storePost(): void
    {
        $this->requireAuth();

        if (!$this->validatePostInput()) {
            $this->redirect('/admin/posts');
        }

        $imagePath = $this->uploadPostImage($_FILES['image'] ?? null);
        if ($imagePath === null && $this->hasSelectedSingleFile($_FILES['image'] ?? null)) {
            $this->redirect('/admin/posts');
        }
        $contentImages = $this->uploadPostContentImages($_FILES['content_images'] ?? null);
        if ($contentImages === null) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            $this->redirect('/admin/posts');
        }
        $inlineImages = $this->persistPostInlineImages((string)($_POST['content'] ?? ''));
        if ($inlineImages === null) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            foreach ($contentImages as $contentImage) {
                $this->removePostImage((string)$contentImage['image_path']);
            }
            $this->redirect('/admin/posts');
        }
        $_POST['content'] = $inlineImages['content'];
        $_POST['created_by'] = $this->currentAdminId();
        $_POST['updated_by'] = $this->currentAdminId();

        try {
            Post::create($_POST, $imagePath ?? '', $contentImages);
        } catch (\Throwable $exception) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            foreach ($contentImages as $contentImage) {
                $this->removePostImage((string)$contentImage['image_path']);
            }
            foreach ($this->extractPostInlineImagePaths((string)($_POST['content'] ?? '')) as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            foreach ($inlineImages['paths'] as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            $this->setPostStatus(false, 'Không thể lưu bài đăng.');
            $this->redirect('/admin/posts');
        }

        $this->setPostStatus(true, 'Đã đăng bài viết.');
        $this->redirect('/admin/posts');
    }

    public function updatePost(): void
    {
        $this->requireAuth();

        $postId = (int)($_POST['id'] ?? 0);
        if ($postId <= 0) {
            $this->setPostStatus(false, 'Không tìm thấy bài đăng cần cập nhật.');
            $this->redirect('/admin/posts');
        }

        if (!$this->validatePostInput()) {
            $this->redirect('/admin/posts?edit=' . $postId);
        }

        $imagePath = null;
        if ($this->hasSelectedSingleFile($_FILES['image'] ?? null)) {
            $imagePath = $this->uploadPostImage($_FILES['image'] ?? null);
            if ($imagePath === null) {
                $this->redirect('/admin/posts?edit=' . $postId);
            }
        }
        $contentImages = $this->uploadPostContentImages($_FILES['content_images'] ?? null);
        if ($contentImages === null) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            $this->redirect('/admin/posts?edit=' . $postId);
        }
        $removeContentImageIds = array_values(array_filter(
            array_map('intval', (array)($_POST['remove_content_images'] ?? [])),
            static fn (int $id): bool => $id > 0
        ));
        $inlineImages = $this->persistPostInlineImages((string)($_POST['content'] ?? ''));
        if ($inlineImages === null) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            foreach ($contentImages as $contentImage) {
                $this->removePostImage((string)$contentImage['image_path']);
            }
            $this->redirect('/admin/posts?edit=' . $postId);
        }
        $_POST['content'] = $inlineImages['content'];
        $_POST['updated_by'] = $this->currentAdminId();

        try {
            $oldPost = Post::update($postId, $_POST, $imagePath, $contentImages, $removeContentImageIds);
        } catch (\Throwable $exception) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            foreach ($contentImages as $contentImage) {
                $this->removePostImage((string)$contentImage['image_path']);
            }
            foreach ($this->extractPostInlineImagePaths((string)($_POST['content'] ?? '')) as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            foreach ($inlineImages['paths'] as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            $this->setPostStatus(false, 'Không thể cập nhật bài đăng.');
            $this->redirect('/admin/posts?edit=' . $postId);
        }

        if (!$oldPost) {
            if ($imagePath) {
                $this->removePostImage($imagePath);
            }
            foreach ($contentImages as $contentImage) {
                $this->removePostImage((string)$contentImage['image_path']);
            }
            foreach ($this->extractPostInlineImagePaths((string)($_POST['content'] ?? '')) as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            foreach ($inlineImages['paths'] as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            $this->setPostStatus(false, 'Không tìm thấy bài đăng cần cập nhật.');
            $this->redirect('/admin/posts');
        }

        if ($imagePath && !empty($oldPost['image_path'])) {
            $this->removePostImage((string)$oldPost['image_path']);
        }
        foreach (($oldPost['content_images'] ?? []) as $oldImage) {
            if (in_array((int)$oldImage['id'], $removeContentImageIds, true)) {
                $this->removePostImage((string)$oldImage['image_path']);
            }
        }
        $oldInlinePaths = $this->extractPostInlineImagePaths((string)($oldPost['content'] ?? ''));
        $newInlinePaths = $this->extractPostInlineImagePaths((string)($_POST['content'] ?? ''));
        foreach (array_diff($oldInlinePaths, $newInlinePaths) as $removedInlinePath) {
            $this->removePostImage((string)$removedInlinePath);
        }

        $this->setPostStatus(true, 'Đã cập nhật bài đăng.');
        $this->redirect('/admin/posts');
    }

    public function uploadPostContentImage(): void
    {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse(['error' => 'Vui lòng đăng nhập quản trị.'], 401);
        }

        $error = '';
        $imagePath = $this->uploadPostInlineImageFile($_FILES['file'] ?? null, $error);
        if (!$imagePath) {
            $this->jsonResponse(['error' => $error ?: 'Không thể tải ảnh lên.'], 400);
        }

        $this->jsonResponse(['location' => '/' . $imagePath]);
    }

    public function updatePostStatus(): void
    {
        $this->requireAuth();

        $postId = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        if ($postId <= 0 || !Post::setStatus($postId, $status)) {
            $this->setPostStatus(false, 'Không thể cập nhật trạng thái bài đăng.');
            $this->redirect('/admin/posts');
        }

        $this->setPostStatus(true, 'Đã cập nhật trạng thái bài đăng.');
        $this->redirect('/admin/posts');
    }

    public function togglePostFeatured(): void
    {
        $this->requireAuth();

        if (!Post::toggleFeatured((int)($_POST['id'] ?? 0))) {
            $this->setPostStatus(false, 'Không thể cập nhật bài viết nổi bật.');
            $this->redirect('/admin/posts');
        }

        $this->setPostStatus(true, 'Đã cập nhật bài viết nổi bật.');
        $this->redirect('/admin/posts');
    }

    public function deletePost(): void
    {
        $this->requireAuth();

        $post = Post::delete((int)($_POST['id'] ?? 0));
        if ($post) {
            $this->removePostImage((string)($post['image_path'] ?? ''));
            foreach (($post['content_images'] ?? []) as $contentImage) {
                $this->removePostImage((string)$contentImage['image_path']);
            }
            foreach ($this->extractPostInlineImagePaths((string)($post['content'] ?? '')) as $inlineImagePath) {
                $this->removePostImage((string)$inlineImagePath);
            }
            $this->setPostStatus(true, 'Đã xóa bài đăng.');
        } else {
            $this->setPostStatus(false, 'Không tìm thấy bài đăng cần xóa.');
        }

        $this->redirect('/admin/posts');
    }

    public function storeDocument(): void
    {
        $this->requireAuth();

        if (trim((string)($_POST['title'] ?? '')) === '') {
            $this->setDocumentStatus(false, 'Vui lòng nhập tên văn bản.');
            $this->redirect('/admin/documents');
        }

        $uploadedFiles = $this->uploadDocuments($_FILES['document_files'] ?? null);
        if (!$uploadedFiles) {
            $this->redirect('/admin/documents');
        }

        try {
            Document::create($_POST, $uploadedFiles);
        } catch (\Throwable $exception) {
            foreach ($uploadedFiles as $uploadedFile) {
                $this->removeDocumentFile($uploadedFile['file_path']);
            }
            $this->setDocumentStatus(false, 'Không thể lưu thông tin văn bản.');
            $this->redirect('/admin/documents');
        }

        $this->setDocumentStatus(true, 'Đã tải văn bản và ' . count($uploadedFiles) . ' tệp đính kèm lên hệ thống.');
        $this->redirect('/admin/documents');
    }

    public function updateDocument(): void
    {
        $this->requireAuth();

        $documentId = (int)($_POST['id'] ?? 0);
        if ($documentId <= 0) {
            $this->setDocumentStatus(false, 'Không tìm thấy văn bản cần cập nhật.');
            $this->redirect('/admin/documents');
        }

        if (trim((string)($_POST['title'] ?? '')) === '') {
            $this->setDocumentStatus(false, 'Vui lòng nhập tên văn bản.');
            $this->redirect('/admin/documents?edit=' . $documentId);
        }

        $uploadedFiles = [];
        $isReplacingFiles = $this->hasSelectedDocumentFiles($_FILES['document_files'] ?? null);
        if ($isReplacingFiles) {
            $uploadedFiles = $this->uploadDocuments($_FILES['document_files'] ?? null) ?? [];
            if (!$uploadedFiles) {
                $this->redirect('/admin/documents?edit=' . $documentId);
            }
        }

        try {
            $removedFiles = Document::update($documentId, $_POST, $uploadedFiles ?: null);
        } catch (\Throwable $exception) {
            foreach ($uploadedFiles as $uploadedFile) {
                $this->removeDocumentFile((string)$uploadedFile['file_path']);
            }
            $this->setDocumentStatus(false, 'Không thể cập nhật thông tin văn bản.');
            $this->redirect('/admin/documents?edit=' . $documentId);
        }

        if ($removedFiles === null) {
            foreach ($uploadedFiles as $uploadedFile) {
                $this->removeDocumentFile((string)$uploadedFile['file_path']);
            }
            $this->setDocumentStatus(false, 'Không tìm thấy văn bản cần cập nhật.');
            $this->redirect('/admin/documents');
        }

        foreach ($removedFiles as $removedFile) {
            $this->removeDocumentFile((string)($removedFile['file_path'] ?? ''));
        }

        $this->setDocumentStatus(
            true,
            $isReplacingFiles
                ? 'Đã cập nhật văn bản và thay tệp đính kèm.'
                : 'Đã cập nhật thông tin văn bản.'
        );
        $this->redirect('/admin/documents');
    }

    public function deleteDocument(): void
    {
        $this->requireAuth();
        $document = Document::delete((int)($_POST['id'] ?? 0));

        if ($document) {
            foreach ($document['attachments'] as $file) {
                $this->removeDocumentFile((string)$file['file_path']);
            }
            $this->setDocumentStatus(true, 'Đã xóa văn bản.');
        } else {
            $this->setDocumentStatus(false, 'Không tìm thấy văn bản cần xóa.');
        }

        $this->redirect('/admin/documents');
    }

    public function changePassword(): void
    {
        $this->requireAuth();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 6) {
            $this->redirect('/admin/password?error=short');
        }

        if ($new !== $confirm) {
            $this->redirect('/admin/password?error=confirm');
        }

        if (!AdminUser::changePassword($current, $new)) {
            $this->redirect('/admin/password?error=current');
        }

        $this->redirect('/admin/password?status=changed');
    }

    public function importExcel(): void
    {
        $this->requireAuth();

        $type = $_POST['type'] ?? '';
        $file = $_FILES['xlsx_file'] ?? null;
        $redirectByType = [
            'leaders' => '/admin/leaders',
            'hamlet_members' => '/admin/hamlet-members',
            'loan_groups' => '/admin/loan-groups',
            'loan_members' => '/admin/loan-members',
        ];
        $redirectPath = $redirectByType[$type] ?? '/admin';

        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['import_status'] = [
                'ok' => false,
                'message' => 'Vui lòng chọn file XLSX hợp lệ.',
            ];
            $this->redirect($redirectPath);
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension !== 'xlsx') {
            $_SESSION['import_status'] = [
                'ok' => false,
                'message' => 'Chỉ hỗ trợ file .xlsx.',
            ];
            $this->redirect($redirectPath);
        }

        try {
            $_SESSION['import_status'] = [
                'ok' => true,
                'message' => 'Import hoàn tất.',
                'result' => ExcelImporter::import($type, $file['tmp_name']),
            ];
        } catch (\Throwable $exception) {
            $_SESSION['import_status'] = [
                'ok' => false,
                'message' => $exception->getMessage(),
            ];
        }

        $this->redirect($redirectPath);
    }

    private function isAuthenticated(): bool
    {
        return !empty($_SESSION['admin_logged_in']);
    }

    private function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/admin?error=login-required');
        }
    }

    private function uploadStaffAvatar(?array $file, int $leaderId = 0): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        unset($finfo);

        if (!isset($allowedMime[$mime])) {
            $_SESSION['flash_error'] = 'Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP.';
            $this->redirect('/admin/leaders?error=avatar');
        }

        $extension = $allowedMime[$mime];
        $filename = sprintf('avatar-%s-%s.%s', bin2hex(random_bytes(6)), time(), $extension);
        $targetDir = realpath(__DIR__ . '/../../public') . '/uploads/avatars';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $_SESSION['flash_error'] = 'Không thể lưu ảnh đại diện.';
            $this->redirect('/admin/leaders?error=avatar');
        }

        if ($leaderId) {
            $existing = Organization::findLeader($leaderId);
            if (str_starts_with($existing['avatar'] ?? '', 'uploads/avatars/')
                && file_exists(realpath(__DIR__ . '/../../public') . '/' . ltrim($existing['avatar'], '/'))) {
                @unlink(realpath(__DIR__ . '/../../public') . '/' . ltrim($existing['avatar'], '/'));
            }
        }

        return 'uploads/avatars/' . $filename;
    }

    private function uploadDocuments(?array $upload): ?array
    {
        if (!$upload || !isset($upload['name']) || !is_array($upload['name'])) {
            $this->setDocumentStatus(false, 'Vui lòng chọn ít nhất một tệp văn bản hợp lệ.');
            return null;
        }

        $uploadedFiles = [];
        foreach ($upload['name'] as $index => $name) {
            if (($upload['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $file = [
                'name' => $name,
                'type' => $upload['type'][$index] ?? '',
                'tmp_name' => $upload['tmp_name'][$index] ?? '',
                'error' => $upload['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $upload['size'][$index] ?? 0,
            ];
            $uploadedFile = $this->uploadDocument($file);
            if (!$uploadedFile) {
                foreach ($uploadedFiles as $storedFile) {
                    $this->removeDocumentFile($storedFile['file_path']);
                }
                return null;
            }
            $uploadedFiles[] = $uploadedFile;
        }

        if (!$uploadedFiles) {
            $this->setDocumentStatus(false, 'Vui lòng chọn ít nhất một tệp văn bản hợp lệ.');
            return null;
        }

        return $uploadedFiles;
    }

    private function hasSelectedDocumentFiles(?array $upload): bool
    {
        if (!$upload || !isset($upload['error']) || !is_array($upload['error'])) {
            return false;
        }

        foreach ($upload['error'] as $error) {
            $code = (int)$error;
            if ($code === UPLOAD_ERR_OK || $code !== UPLOAD_ERR_NO_FILE) {
                return true;
            }
        }

        return false;
    }

    private function hasSelectedSingleFile(?array $file): bool
    {
        return $file && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function uploadPostImage(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $this->setPostStatus(false, 'Vui lòng chọn ảnh bài đăng hợp lệ.');
            return null;
        }

        if ((int)($file['size'] ?? 0) > 2 * 1024 * 1024) {
            $this->setPostStatus(false, 'Ảnh bài đăng không được vượt quá 2 MB.');
            return null;
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        unset($finfo);

        if (!isset($allowedMime[$mime])) {
            $this->setPostStatus(false, 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
            return null;
        }

        $publicRoot = realpath(__DIR__ . '/../../public');
        if (!$publicRoot) {
            $this->setPostStatus(false, 'Không thể xác định thư mục lưu ảnh.');
            return null;
        }

        $targetDir = $publicRoot . '/uploads/posts';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            $this->setPostStatus(false, 'Không thể tạo thư mục lưu ảnh bài đăng.');
            return null;
        }

        $filename = sprintf('post-%s-%s.%s', bin2hex(random_bytes(6)), time(), $allowedMime[$mime]);
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
            $this->setPostStatus(false, 'Không thể lưu ảnh bài đăng.');
            return null;
        }

        return 'uploads/posts/' . $filename;
    }

    private function uploadPostContentImages(?array $upload): ?array
    {
        if (!$upload || !isset($upload['name']) || !is_array($upload['name'])) {
            return [];
        }

        $images = [];
        foreach ($upload['name'] as $index => $name) {
            if (($upload['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $file = [
                'name' => $name,
                'type' => $upload['type'][$index] ?? '',
                'tmp_name' => $upload['tmp_name'][$index] ?? '',
                'error' => $upload['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $upload['size'][$index] ?? 0,
            ];
            $imagePath = $this->uploadPostImage($file);
            if ($imagePath === null) {
                foreach ($images as $image) {
                    $this->removePostImage((string)$image['image_path']);
                }
                return null;
            }
            $images[] = [
                'image_path' => $imagePath,
                'caption' => '',
            ];
        }

        return $images;
    }

    private function uploadPostInlineImageFile(?array $file, string &$error = ''): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $error = 'Vui lòng chọn ảnh nội dung hợp lệ.';
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $error = 'Vui lòng chọn ảnh nội dung hợp lệ.';
            return null;
        }

        if ((int)($file['size'] ?? 0) > 2 * 1024 * 1024) {
            $error = 'Ảnh trong nội dung không được vượt quá 2 MB.';
            return null;
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        unset($finfo);

        if (!isset($allowedMime[$mime])) {
            $error = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.';
            return null;
        }

        $publicRoot = realpath(__DIR__ . '/../../public');
        if (!$publicRoot) {
            $error = 'Không thể xác định thư mục lưu ảnh.';
            return null;
        }

        $targetDir = $publicRoot . '/uploads/posts';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            $error = 'Không thể tạo thư mục lưu ảnh bài đăng.';
            return null;
        }

        $filename = sprintf('post-inline-%s-%s.%s', bin2hex(random_bytes(6)), time(), $allowedMime[$mime]);
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
            $error = 'Không thể lưu ảnh trong nội dung.';
            return null;
        }

        return 'uploads/posts/' . $filename;
    }

    private function persistPostInlineImages(string $content): ?array
    {
        $storedPaths = [];
        $updatedContent = preg_replace_callback(
            '#src=(["\'])data:image/(png|jpe?g|webp|gif);base64,([^"\']+)\1#i',
            function (array $match) use (&$storedPaths): string {
                $extensionByType = [
                    'png' => 'png',
                    'jpg' => 'jpg',
                    'jpeg' => 'jpg',
                    'webp' => 'webp',
                    'gif' => 'gif',
                ];
                $type = strtolower($match[2]);
                $extension = $extensionByType[$type] ?? null;
                if (!$extension) {
                    $this->setPostStatus(false, 'Ảnh trong nội dung không đúng định dạng.');
                    return $match[0];
                }

                $binary = base64_decode($match[3], true);
                if ($binary === false || $binary === '') {
                    $this->setPostStatus(false, 'Không thể đọc ảnh trong nội dung.');
                    return $match[0];
                }

                if (strlen($binary) > 2 * 1024 * 1024) {
                    $this->setPostStatus(false, 'Mỗi ảnh trong nội dung không được vượt quá 2 MB.');
                    return $match[0];
                }

                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = (string)$finfo->buffer($binary);
                unset($finfo);
                $allowedMime = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif',
                ];
                if (!isset($allowedMime[$mime])) {
                    $this->setPostStatus(false, 'Ảnh trong nội dung không đúng định dạng.');
                    return $match[0];
                }

                $publicRoot = realpath(__DIR__ . '/../../public');
                if (!$publicRoot) {
                    $this->setPostStatus(false, 'Không thể xác định thư mục lưu ảnh.');
                    return $match[0];
                }

                $targetDir = $publicRoot . '/uploads/posts';
                if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
                    $this->setPostStatus(false, 'Không thể tạo thư mục lưu ảnh bài đăng.');
                    return $match[0];
                }

                $filename = sprintf('post-inline-%s-%s.%s', bin2hex(random_bytes(6)), time(), $allowedMime[$mime]);
                $relativePath = 'uploads/posts/' . $filename;
                if (file_put_contents($targetDir . '/' . $filename, $binary) === false) {
                    $this->setPostStatus(false, 'Không thể lưu ảnh trong nội dung.');
                    return $match[0];
                }

                $storedPaths[] = $relativePath;
                return 'src="/' . $relativePath . '"';
            },
            $content
        );

        if ($updatedContent === null) {
            foreach ($storedPaths as $storedPath) {
                $this->removePostImage($storedPath);
            }
            $this->setPostStatus(false, 'Không thể xử lý ảnh trong nội dung.');
            return null;
        }

        if (preg_match('#src=(["\'])data:image/#i', $updatedContent)) {
            foreach ($storedPaths as $storedPath) {
                $this->removePostImage($storedPath);
            }
            return null;
        }

        return [
            'content' => $updatedContent,
            'paths' => $storedPaths,
        ];
    }

    private function extractPostInlineImagePaths(string $content): array
    {
        preg_match_all('#src=(["\'])/?(uploads/posts/post-inline-[^"\']+)\1#i', $content, $matches);

        return array_values(array_unique($matches[2] ?? []));
    }

    private function removePostImage(string $relativePath): void
    {
        if ($relativePath === '') {
            return;
        }

        $publicRoot = realpath(__DIR__ . '/../../public');
        $postDir = $publicRoot ? realpath($publicRoot . '/uploads/posts') : false;
        $filePath = $publicRoot ? realpath($publicRoot . '/' . ltrim($relativePath, '/')) : false;

        if ($filePath && $postDir && str_starts_with($filePath, $postDir . DIRECTORY_SEPARATOR)) {
            @unlink($filePath);
        }
    }

    private function validatePostInput(): bool
    {
        $errors = [];
        if (trim((string)($_POST['title'] ?? '')) === '') {
            $errors[] = 'Vui lòng nhập tiêu đề bài đăng.';
        }

        if (trim((string)($_POST['content'] ?? '')) === '') {
            $errors[] = 'Vui lòng nhập nội dung bài đăng.';
        }

        $status = (string)($_POST['status'] ?? 'published');
        if (!in_array($status, Post::STATUSES, true)) {
            $errors[] = 'Trạng thái bài đăng không hợp lệ.';
        }

        $publishedAt = trim((string)($_POST['published_at'] ?? ''));
        if ($publishedAt !== '' && strtotime($publishedAt) === false) {
            $errors[] = 'Thời gian đăng không hợp lệ.';
        }

        foreach (['title' => 255, 'slug' => 255, 'meta_title' => 255, 'meta_description' => 255] as $field => $maxLength) {
            if (strlen(trim((string)($_POST[$field] ?? ''))) > $maxLength) {
                $errors[] = 'Trường ' . $field . ' không được vượt quá ' . $maxLength . ' ký tự.';
            }
        }

        if ($errors) {
            $this->setPostStatus(false, $errors[0]);
            $this->setPostValidationErrors($errors);
            $this->rememberPostOldInput($_POST);
            return false;
        }

        return true;
    }

    private function uploadDocument(?array $file): ?array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $this->setDocumentStatus(false, 'Vui lòng chọn tệp văn bản hợp lệ.');
            return null;
        }

        if ((int)($file['size'] ?? 0) > 10 * 1024 * 1024) {
            $this->setDocumentStatus(false, 'Tệp văn bản không được vượt quá 10 MB.');
            return null;
        }

        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedExtensions = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/vnd.ms-office', 'application/x-ole-storage', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
            'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office', 'application/x-ole-storage', 'application/octet-stream'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        unset($finfo);

        if (!isset($allowedExtensions[$extension]) || !in_array($mime, $allowedExtensions[$extension], true)) {
            $this->setDocumentStatus(false, 'Chỉ chấp nhận tệp PDF, DOC, DOCX, XLS hoặc XLSX.');
            return null;
        }

        $publicRoot = realpath(__DIR__ . '/../../public');
        if (!$publicRoot) {
            $this->setDocumentStatus(false, 'Không thể xác định thư mục lưu tệp.');
            return null;
        }

        $targetDir = $publicRoot . '/uploads/documents';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            $this->setDocumentStatus(false, 'Không thể tạo thư mục lưu văn bản.');
            return null;
        }

        $filename = sprintf('document-%s-%s.%s', bin2hex(random_bytes(6)), time(), $extension);
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
            $this->setDocumentStatus(false, 'Không thể lưu tệp văn bản.');
            return null;
        }

        return [
            'original_name' => basename((string)$file['name']),
            'file_path' => 'uploads/documents/' . $filename,
            'file_size' => (int)$file['size'],
            'mime_type' => $mime,
        ];
    }

    private function removeDocumentFile(string $relativePath): void
    {
        $publicRoot = realpath(__DIR__ . '/../../public');
        $documentDir = $publicRoot ? realpath($publicRoot . '/uploads/documents') : false;
        $filePath = $publicRoot ? realpath($publicRoot . '/' . ltrim($relativePath, '/')) : false;

        if ($filePath && $documentDir && str_starts_with($filePath, $documentDir . DIRECTORY_SEPARATOR)) {
            @unlink($filePath);
        }
    }

    private function renderAdmin(string $view, string $title, array $data = []): void
    {
        $this->requireAuth();
        $this->view($view, ['title' => $title] + $data, 'admin');
    }

    private function refreshLoginCaptcha(): void
    {
        $captcha = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $_SESSION['admin_login_captcha_question'] = $captcha;
        $_SESSION['admin_login_captcha_answer'] = $captcha;
        $_SESSION['admin_login_captcha_signature'] = $this->signLoginCaptcha($captcha);
    }

    private function isValidLoginCaptchaSignature(string $captcha): bool
    {
        $signature = (string)($_POST['captcha_signature'] ?? '');

        return $signature !== '' && hash_equals($this->signLoginCaptcha($captcha), $signature);
    }

    private function signLoginCaptcha(string $captcha): string
    {
        return hash_hmac('sha256', $captcha, $this->loginCaptchaKey());
    }

    private function loginCaptchaKey(): string
    {
        return (string)app_config('login_captcha_key', 'mttq-tanhoa-admin-login');
    }

    private function isLoginRateLimited(): bool
    {
        $attempts = $_SESSION['admin_login_attempts'] ?? [];
        $windowStart = time() - 600;
        $attempts = array_values(array_filter(
            is_array($attempts) ? $attempts : [],
            static fn ($timestamp): bool => (int)$timestamp >= $windowStart
        ));
        $_SESSION['admin_login_attempts'] = $attempts;

        return count($attempts) >= 5;
    }

    private function recordFailedLogin(): void
    {
        $attempts = $_SESSION['admin_login_attempts'] ?? [];
        $attempts = is_array($attempts) ? $attempts : [];
        $attempts[] = time();
        $_SESSION['admin_login_attempts'] = array_slice($attempts, -5);
    }

    private function clearFailedLogins(): void
    {
        unset($_SESSION['admin_login_attempts']);
    }

    private function currentAdminId(): ?int
    {
        $id = (int)($_SESSION['admin_user_id'] ?? $_SESSION['admin_id'] ?? 0);

        return $id > 0 ? $id : null;
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

    private function consumeImportStatus(): ?array
    {
        $status = $_SESSION['import_status'] ?? null;
        unset($_SESSION['import_status']);
        return $status;
    }

    private function setDocumentStatus(bool $ok, string $message): void
    {
        $_SESSION['document_status'] = ['ok' => $ok, 'message' => $message];
    }

    private function consumeDocumentStatus(): ?array
    {
        $status = $_SESSION['document_status'] ?? null;
        unset($_SESSION['document_status']);
        return $status;
    }

    private function setPostStatus(bool $ok, string $message): void
    {
        $_SESSION['post_status'] = ['ok' => $ok, 'message' => $message];
    }

    private function consumePostStatus(): ?array
    {
        $status = $_SESSION['post_status'] ?? null;
        unset($_SESSION['post_status']);
        return $status;
    }

    private function setPostValidationErrors(array $errors): void
    {
        $_SESSION['post_errors'] = array_values(array_filter(array_map('strval', $errors)));
    }

    private function consumePostErrors(): array
    {
        $errors = $_SESSION['post_errors'] ?? [];
        unset($_SESSION['post_errors']);

        return is_array($errors) ? $errors : [];
    }

    private function rememberPostOldInput(array $input): void
    {
        unset($input['_token']);
        $_SESSION['post_old_input'] = $input;
    }

    private function consumePostOldInput(): array
    {
        $input = $_SESSION['post_old_input'] ?? [];
        unset($_SESSION['post_old_input']);

        return is_array($input) ? $input : [];
    }

    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

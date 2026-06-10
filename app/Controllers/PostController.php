<?php

namespace App\Controllers;

use App\Models\Post;

class PostController extends BaseController
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 9;
        $pagination = Post::publishedPaginated($page, $limit);

        $this->view('posts/index', [
            'title' => 'Bài đăng',
            'metaDescription' => 'Tin tức, hoạt động và bài đăng của Ủy ban MTTQ Việt Nam xã Tân Hòa.',
            'posts' => $pagination['items'],
            'featuredPosts' => Post::featured(4),
            'total' => $pagination['total'],
            'page' => $pagination['page'],
            'limit' => $limit,
            'totalPages' => $pagination['totalPages'],
        ]);
    }

    public function show(): void
    {
        $post = Post::findPublished((int)($_GET['id'] ?? 0));
        $this->renderPostDetail($post);
    }

    public function showBySlug(string $slug): void
    {
        $post = Post::findPublishedBySlug($slug);
        $this->renderPostDetail($post);
    }

    private function renderPostDetail(?array $post): void
    {
        if (!$post) {
            http_response_code(404);
        }
        if ($post) {
            Post::incrementViews((int)$post['id']);
            $post['views'] = (int)($post['views'] ?? 0) + 1;
        }

        $description = $post ? (string)($post['meta_description'] ?: $post['excerpt'] ?: $post['title']) : 'Không tìm thấy bài đăng.';
        $postUrl = $post ? app_url(post_public_url($post)) : app_url('/posts');
        $imageUrl = $post ? app_url('/' . ltrim((string)($post['thumbnail'] ?: $post['image_path'] ?: 'img/logo-mttq.png'), '/')) : app_url('/img/logo-mttq.png');

        $this->view('posts/show', [
            'title' => $post ? (string)($post['meta_title'] ?: $post['title']) : 'Không tìm thấy bài đăng',
            'metaDescription' => $description,
            'canonicalUrl' => $postUrl,
            'post' => $post,
            'relatedPosts' => $post ? Post::latest(6) : [],
            'og' => $post ? [
                'title' => (string)($post['meta_title'] ?: $post['title']),
                'description' => $description,
                'image' => (string)($post['thumbnail'] ?: $post['image_path'] ?: 'img/logo-mttq.png'),
                'type' => 'article',
            ] : [],
            'jsonLd' => $post ? [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'NewsArticle',
                    'headline' => (string)$post['title'],
                    'description' => $description,
                    'image' => [$imageUrl],
                    'datePublished' => date(DATE_ATOM, strtotime((string)$post['published_at'])),
                    'dateModified' => date(DATE_ATOM, strtotime((string)($post['updated_at'] ?? $post['published_at']))),
                    'mainEntityOfPage' => $postUrl,
                    'author' => [
                        '@type' => 'Organization',
                        'name' => 'Ủy ban MTTQ Việt Nam xã Tân Hòa',
                    ],
                    'publisher' => [
                        '@type' => 'GovernmentOrganization',
                        'name' => 'Ủy ban MTTQ Việt Nam xã Tân Hòa',
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => app_url('/img/logo-mttq.png'),
                        ],
                    ],
                ],
            ] : null,
        ]);
    }
}

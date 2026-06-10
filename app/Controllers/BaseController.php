<?php

namespace App\Controllers;

class BaseController
{
    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../../resources/views/' . $view . '.php';
        require __DIR__ . '/../../resources/views/layouts/' . $layout . '.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

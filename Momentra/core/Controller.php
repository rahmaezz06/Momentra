<?php

abstract class Controller {

    protected function view(string $view, array $data = []): void {
        extract($data);
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            die("View not found: $view");
        }
        require $viewPath;
    }

    protected function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    protected function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireLogin(): void {
        if (!isLoggedIn()) {
            $this->redirect(BASE_URL . '/login');
        }
    }

    protected function currentUser(): ?array {
        return currentUser();
    }
}

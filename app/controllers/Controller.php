<?php

class Controller
{
    public function view(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }

    protected function redirect(string $url): never
    {
        if (ob_get_level() > 0) ob_end_clean();
        header('Location: ' . $url);
        exit;
    }
}

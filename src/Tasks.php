<?php
namespace App;

class Tasks
{
    public static function health(): array
    {
        return [
            'status'  => 'ok',
            'version' => getenv('APP_VERSION') ?: 'dev',
            'service' => 'todo-app',
        ];
    }

    public static function isValidTitle(string $title): bool
    {
        $title = trim($title);
        return $title !== '' && strlen($title) <= 100;
    }

    /** Décide quelle action exécuter selon la méthode HTTP et le chemin. */
    public static function route(string $method, string $path): array
    {
        if ($path === '/') {
            return ['action' => 'home'];
        }
        if ($path === '/health') {
            return ['action' => 'health'];
        }
        if ($path === '/tasks' && $method === 'GET') {
            return ['action' => 'list'];
        }
        if ($path === '/tasks' && $method === 'POST') {
            return ['action' => 'create'];
        }
        if ($method === 'POST' && preg_match('#^/tasks/(\d+)/toggle$#', $path, $m)) {
            return ['action' => 'toggle', 'id' => (int) $m[1]];
        }
        if ($method === 'DELETE' && preg_match('#^/tasks/(\d+)$#', $path, $m)) {
            return ['action' => 'delete', 'id' => (int) $m[1]];
        }
        return ['action' => 'not_found'];
    }
}
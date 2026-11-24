<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Tasks;

function respond(int $status, array $data): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function db(): PDO
{
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_NAME') ?: 'todo'),
        getenv('DB_USER') ?: 'todo',
        getenv('DB_PASSWORD') ?: 'todo',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        done TINYINT(1) NOT NULL DEFAULT 0)');
    return $pdo;
}

$path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = Tasks::route($_SERVER['REQUEST_METHOD'], $path);

switch ($route['action']) {
    case 'home':
        $page = __DIR__ . '/index.html';
        if (!is_file($page)) {
            respond(404, ['error' => 'page not found']);
        }
        header('Content-Type: text/html; charset=utf-8');
        readfile($page);
        break;

    case 'health':
        respond(200, Tasks::health());

    case 'list':
        $rows = db()->query('SELECT id, title, done FROM tasks ORDER BY id DESC')
                    ->fetchAll(PDO::FETCH_ASSOC);
        respond(200, array_map(fn($r) => [
            'id'    => (int) $r['id'],
            'title' => $r['title'],
            'done'  => (bool) $r['done'],
        ], $rows));

    case 'create':
        $title = $_POST['title'] ?? '';
        if (!is_string($title) || !Tasks::isValidTitle($title)) {
            respond(400, ['error' => 'invalid title']);
        }
        $title = trim($title);
        $pdo = db();
        $pdo->prepare('INSERT INTO tasks (title) VALUES (?)')->execute([$title]);
        respond(201, ['id' => (int) $pdo->lastInsertId(), 'title' => $title, 'done' => false]);

    case 'toggle':
        $stmt = db()->prepare('UPDATE tasks SET done = NOT done WHERE id = ?');
        $stmt->execute([$route['id']]);
        if ($stmt->rowCount() === 0) {
            respond(404, ['error' => 'task not found']);
        }
        respond(200, ['toggled' => true]);

    case 'delete':
        $stmt = db()->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([$route['id']]);
        if ($stmt->rowCount() === 0) {
            respond(404, ['error' => 'task not found']);
        }
        respond(200, ['deleted' => true]);

    default:
        respond(404, ['error' => 'not found']);
}
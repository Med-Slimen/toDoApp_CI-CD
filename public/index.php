<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Tasks;

header('Content-Type: application/json');
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($path === '/health') {
    echo json_encode(Tasks::health());
    exit;
}

if ($path === '/tasks') {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_NAME') ?: 'todo'),
        getenv('DB_USER') ?: 'todo',
        getenv('DB_PASSWORD') ?: 'todo'
    );
    $pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL)');

    if ($method === 'POST') {
        $title = $_POST['title'] ?? '';
        if (!Tasks::isValidTitle($title)) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid title']);
            exit;
        }
        $pdo->prepare('INSERT INTO tasks (title) VALUES (?)')->execute([trim($title)]);
        http_response_code(201);
        echo json_encode(['created' => true]);
        exit;
    }

    echo json_encode($pdo->query('SELECT id, title FROM tasks')->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'not found']);

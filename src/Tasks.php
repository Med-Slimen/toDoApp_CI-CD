<?php
namespace App;

class Tasks
{
    public static function health(): array
    {
        return [
            'status'  => 'ok',
            'version' => getenv('APP_VERSION') ?: 'dev',
        ];
    }

    public static function isValidTitle(string $title): bool
    {
        $title = trim($title);
        return $title !== '' && strlen($title) <= 100;
    }
}

<?php
require_once __DIR__ . '/config.php';

$login    = 'admin';
$password = 'admin123';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (login, password_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$stmt->execute([$login, $hash]);

echo "Администратор создан: {$login} / {$password}";

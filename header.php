<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Светлые Сны</title>
</head>
<body class="container">
<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php"
       class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Светлые Сны</span>
    </a>

    <ul class="nav">
        <?php if (is_admin()): ?>
            <li class="nav-item"><a href="admin.php" class="nav-link">Панель администратора</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Выйти</a></li>
        <?php else: ?>
            <li class="nav-item"><a href="login.php" class="nav-link">Вход для администратора</a></li>
        <?php endif; ?>
        <li class="nav-item"><a href="#" class="nav-link">Приезжайте как гости, уезжайте как друзья!</a></li>
    </ul>
</header>

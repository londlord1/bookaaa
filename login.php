<?php
require_once __DIR__ . '/config.php';

if (is_admin()) {
    header('Location: admin.php');
    exit;
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Недействительный запрос. Обновите страницу.';
    } else {
        $login    = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($login === '' || $password === '') {
            $error = 'Заполните логин и пароль.';
        } else {
            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE login = ?');
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$user['id'];
                header('Location: admin.php');
                exit;
            }
            $error = 'Неверный логин или пароль.';
        }
    }
}

include __DIR__ . '/header.php';
?>
<main>
    <div class="d-flex justify-content-between flex-wrap align-items-center">
        <h1>Вход</h1>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="my-2" novalidate method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="my-2">
            <label for="username" class="form-label">Логин</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= e($login) ?>" required>
        </div>
        <div class="my-2">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="d-grid gap-2">
            <button class="btn btn-primary">Войти</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/footer.php'; ?>

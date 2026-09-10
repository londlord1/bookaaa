<?php
require_once __DIR__ . '/config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

/* --- Модерация заявок --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $_SESSION['flash'] = 'Недействительный запрос.';
    } else {
        $id     = (int)($_POST['id'] ?? 0);
        $action = (string)($_POST['action'] ?? '');

        if ($id > 0 && $action === 'approve') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = 'Заявка №' . $id . ' одобрена.';
        } elseif ($id > 0 && $action === 'delete') {
            $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['flash'] = 'Заявка №' . $id . ' удалена.';
        } else {
            $_SESSION['flash'] = 'Некорректное действие.';
        }
    }
    header('Location: admin.php');
    exit;
}

$bookings = $pdo->query(
    'SELECT b.*, r.category, r.price
       FROM bookings b
       JOIN rooms r ON r.id = b.room_id
      ORDER BY b.id DESC'
)->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include __DIR__ . '/header.php';
?>
<main>
    <div class="d-flex justify-content-between flex-wrap align-items-center">
        <h1>Панель администратора</h1>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-info" role="alert"><?= e($flash) ?></div>
    <?php endif; ?>

    <?php if (!$bookings): ?>
        <div class="alert alert-warning text-center" role="alert">Заявок пока нет.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-around flex-wrap align-items-center">
        <?php foreach ($bookings as $b): ?>
            <div class="card">
                <div class="card-body">
                    <h5>Заявка №<?= (int)$b['id'] ?></h5>
                    <h5>Фамилия: <?= e($b['last_name']) ?></h5>
                    <h5>Имя: <?= e($b['first_name']) ?></h5>
                    <h5>Телефон: <?= e($b['phone']) ?></h5>
                    <h5>Почта: <?= e($b['email']) ?></h5>
                    <ul class="list-group">
                        <li class="list-group-item">Номер: <?= e($b['category']) ?> (<?= (int)$b['price'] ?> ₽ / чел)</li>
                        <li class="list-group-item">
                            Дата заезда: <?= e(date('d.m.Y', strtotime($b['date_in']))) ?>
                        </li>
                        <li class="list-group-item">
                            Дата выезда: <?= e(date('d.m.Y', strtotime($b['date_out']))) ?>
                        </li>
                        <li class="list-group-item">
                            Статус:
                            <?php if ($b['status'] === 'approved'): ?>
                                <span class="badge bg-success">Одобрена</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Новая</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="d-grid gap-2">
                    <form method="post" action="admin.php" class="d-grid gap-2">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                        <button class="btn btn-success" name="action" value="approve"
                            <?= $b['status'] === 'approved' ? 'disabled' : '' ?>>Одобрить</button>
                        <button class="btn btn-danger" name="action" value="delete"
                                onclick="return confirm('Удалить заявку №<?= (int)$b['id'] ?>?');">Удалить</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>

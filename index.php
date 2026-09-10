<?php
require_once __DIR__ . '/config.php';

/* --- Фильтрация на стороне сервера --- */
$categories = $pdo->query('SELECT DISTINCT category FROM rooms ORDER BY category')
                  ->fetchAll(PDO::FETCH_COLUMN);

$category = trim((string)($_GET['category'] ?? ''));
if ($category !== '' && !in_array($category, $categories, true)) {
    $category = '';
}

if ($category !== '') {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE category = ? ORDER BY id');
    $stmt->execute([$category]);
} else {
    $stmt = $pdo->query('SELECT * FROM rooms ORDER BY id');
}
$rooms = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>
<main>
    <div class="d-flex justify-content-between flex-wrap">
        <h1>Каталог номеров</h1>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                Категории<?= $category !== '' ? ': ' . e($category) : '' ?>
            </button>
            <ul class="dropdown-menu">
                <?php foreach ($categories as $c): ?>
                    <li>
                        <a class="dropdown-item" href="index.php?category=<?= urlencode($c) ?>">
                            <?= e($c) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php" class="btn btn-danger my-1">Сбросить фильтр</a>
        </div>
    </div>

    <div class="d-flex justify-content-around flex-wrap align-items-center">
        <?php if (!$rooms): ?>
            <div class="alert alert-warning w-100 text-center" role="alert">
                По выбранной категории номера не найдены.
            </div>
        <?php endif; ?>

        <?php foreach ($rooms as $room): ?>
            <div class="card">
                <img src="<?= e($room['image']) ?>" class="card-img-top" alt="<?= e($room['category']) ?>">
                <div class="card-body">
                    <h3>Категория: <?= e($room['category']) ?></h3>
                    <h5>Цена: <?= (int)$room['price'] ?> ₽ / чел</h5>
                    <h5>Характеристики:</h5>
                    <ul class="list-group">
                        <?php foreach (explode('|', $room['features']) as $feature): ?>
                            <li class="list-group-item"><?= e($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="d-grid gap-2">
                    <a href="order.php?room_id=<?= (int)$room['id'] ?>" class="btn btn-success">Забронировать</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>

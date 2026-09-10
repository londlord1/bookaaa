<?php
require_once __DIR__ . '/config.php';

$rooms = $pdo->query('SELECT id, category, price FROM rooms ORDER BY id')->fetchAll();
$roomIds = array_map('intval', array_column($rooms, 'id'));

$errors  = [];
$success = false;

$data = [
    'room_id'    => (int)($_GET['room_id'] ?? 0),
    'first_name' => '',
    'last_name'  => '',
    'phone'      => '',
    'email'      => '',
    'date_in'    => '',
    'date_out'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors['common'] = 'Недействительный запрос. Обновите страницу и попробуйте снова.';
    } else {
        $data['room_id']    = (int)($_POST['room_id'] ?? 0);
        $data['first_name'] = trim((string)($_POST['first_name'] ?? ''));
        $data['last_name']  = trim((string)($_POST['last_name'] ?? ''));
        $data['phone']      = trim((string)($_POST['phone'] ?? ''));
        $data['email']      = trim((string)($_POST['email'] ?? ''));
        $data['date_in']    = trim((string)($_POST['date_in'] ?? ''));
        $data['date_out']   = trim((string)($_POST['date_out'] ?? ''));

        /* --- Номер --- */
        if (!in_array($data['room_id'], $roomIds, true)) {
            $errors['room_id'] = 'Пожалуйста, выберите номер.';
        }

        /* --- Имя / Фамилия --- */
        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['first_name']) > 50
            || !preg_match('/^[\p{L}\s\-]+$/u', $data['first_name'])) {
            $errors['first_name'] = 'Пожалуйста, введите имя (только буквы, 2–50 символов).';
        }
        if (mb_strlen($data['last_name']) < 2 || mb_strlen($data['last_name']) > 50
            || !preg_match('/^[\p{L}\s\-]+$/u', $data['last_name'])) {
            $errors['last_name'] = 'Пожалуйста, введите фамилию (только буквы, 2–50 символов).';
        }

        /* --- Телефон --- */
        if (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $data['phone'])) {
            $errors['phone'] = 'Телефон в формате +7(999)999-99-99.';
        }

        /* --- Почта --- */
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 100) {
            $errors['email'] = 'Пожалуйста, введите корректный email.';
        }

        /* --- Даты --- */
        $in  = DateTime::createFromFormat('Y-m-d', $data['date_in']);
        $out = DateTime::createFromFormat('Y-m-d', $data['date_out']);
        $today = new DateTime('today');

        if (!$in) {
            $errors['date_in'] = 'Пожалуйста, введите дату заезда.';
        } elseif ($in < $today) {
            $errors['date_in'] = 'Дата заезда не может быть в прошлом.';
        }

        if (!$out) {
            $errors['date_out'] = 'Пожалуйста, введите дату выезда.';
        } elseif ($in && $out <= $in) {
            $errors['date_out'] = 'Дата выезда должна быть позже даты заезда.';
        }

        /* --- Сохранение --- */
        if (!$errors) {
            $stmt = $pdo->prepare(
                'INSERT INTO bookings (room_id, first_name, last_name, phone, email, date_in, date_out)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $data['room_id'],
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $data['email'],
                $data['date_in'],
                $data['date_out'],
            ]);

            $success = true;
            $data = array_map(static fn($v) => '', $data);
            $data['room_id'] = 0;
        }
    }
}

include __DIR__ . '/header.php';
?>
<main>
    <div class="d-flex justify-content-between flex-wrap align-items-center">
        <h1>Бронирование номера</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success text-center" role="alert">
            Заявка успешно отправлена! Ожидайте подтверждения администратора.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors['common'])): ?>
        <div class="alert alert-danger" role="alert"><?= e($errors['common']) ?></div>
    <?php endif; ?>

    <?php if ($errors && empty($errors['common'])): ?>
        <div class="alert alert-danger" role="alert">
            Заявка не отправлена. Исправьте ошибки в форме.
        </div>
    <?php endif; ?>

    <form class="row g-3 needs-validation my-2" novalidate method="post" action="order.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="col-md-4">
            <label for="room_id" class="form-label">Категория номера</label>
            <select class="form-select <?= isset($errors['room_id']) ? 'is-invalid' : '' ?>"
                    id="room_id" name="room_id" required>
                <option value="">— выберите номер —</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?= (int)$room['id'] ?>"
                        <?= $data['room_id'] === (int)$room['id'] ? 'selected' : '' ?>>
                        <?= e($room['category']) ?> — <?= (int)$room['price'] ?> ₽ / чел
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= e($errors['room_id'] ?? 'Пожалуйста, выберите номер.') ?></div>
        </div>

        <div class="col-md-4">
            <label for="first_name" class="form-label">Имя</label>
            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                   id="first_name" name="first_name" value="<?= e($data['first_name']) ?>" required>
            <div class="invalid-feedback"><?= e($errors['first_name'] ?? 'Пожалуйста, введите имя.') ?></div>
        </div>

        <div class="col-md-4">
            <label for="last_name" class="form-label">Фамилия</label>
            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                   id="last_name" name="last_name" value="<?= e($data['last_name']) ?>" required>
            <div class="invalid-feedback"><?= e($errors['last_name'] ?? 'Пожалуйста, введите фамилию.') ?></div>
        </div>

        <div class="col-md-4">
            <label for="validationCustomPhone" class="form-label">Телефон</label>
            <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                   id="validationCustomPhone" name="phone" value="<?= e($data['phone']) ?>" required>
            <div class="invalid-feedback"><?= e($errors['phone'] ?? 'Пожалуйста, введите номер телефона.') ?></div>
        </div>

        <div class="col-md-8">
            <label for="email" class="form-label">Почта</label>
            <input type="text" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   id="email" name="email" value="<?= e($data['email']) ?>" required>
            <div class="invalid-feedback"><?= e($errors['email'] ?? 'Пожалуйста, введите email.') ?></div>
        </div>

        <div class="col-md-6">
            <label for="date_in" class="form-label">Дата заезда</label>
            <input type="date" class="form-control <?= isset($errors['date_in']) ? 'is-invalid' : '' ?>"
                   id="date_in" name="date_in" min="<?= date('Y-m-d') ?>"
                   value="<?= e($data['date_in']) ?>" required>
            <div class="invalid-feedback"><?= e($errors['date_in'] ?? 'Пожалуйста, введите дату заезда.') ?></div>
        </div>

        <div class="col-md-6">
            <label for="date_out" class="form-label">Дата выезда</label>
            <input type="date" class="form-control <?= isset($errors['date_out']) ? 'is-invalid' : '' ?>"
                   id="date_out" name="date_out"
                   value="<?= e($data['date_out']) ?>" required>
            <div class="invalid-feedback"><?= e($errors['date_out'] ?? 'Пожалуйста, введите дату выезда.') ?></div>
        </div>

        <div class="d-grid gap-2">
            <button class="btn btn-primary">Отправить заявку</button>
        </div>
    </form>
</main>
<?php include __DIR__ . '/footer.php'; ?>

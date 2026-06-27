<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Анкета разработчика (Задание 4)</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="tel"], input[type="email"], input[type="date"], select, textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        /* Подсветка полей с ошибками красным */
        .error-field { border: 2px solid red !important; background-color: #ffe6e6; }
        .error-msg { color: red; font-size: 12px; margin-top: 5px; font-weight: bold; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .radio-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
        .checkbox-group { display: flex; align-items: center; }
        .checkbox-group input { width: auto; margin-right: 10px; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;}
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h1>Анкета разработчика</h1>

    <?php if (isset($_GET['save']) && $_GET['save'] == 1): ?>
        <div class="success-msg">✅ Данные успешно сохранены в базу данных!</div>
    <?php endif; ?>

    <?php
    // Чтение и УДАЛЕНИЕ Cookies с ошибками (до конца сессии)
    $errors = [];
    $fields = ['fio', 'phone', 'email', 'birth_date', 'gender', 'languages', 'biography', 'contract'];
    foreach ($fields as $f) {
        if (isset($_COOKIE['error_' . $f])) {
            $errors[$f] = $_COOKIE['error_' . $f];
            setcookie('error_' . $f, '', time() - 3600, '/'); // Удаляем cookie
        }
    }

    // Чтение Cookies с данными (на 1 год)
    $data = [];
    foreach ($fields as $f) {
        if (isset($_COOKIE['data_' . $f])) {
            $data[$f] = $_COOKIE['data_' . $f];
        }
    }
    $saved_langs = isset($data['languages']) && $data['languages'] !== '' ? explode(',', $data['languages']) : [];
    ?>

    <form action="index.php" method="POST">
        <div class="form-group">
            <label>ФИО</label>
            <input type="text" name="fio" value="<?= htmlspecialchars($data['fio'] ?? '') ?>" class="<?= isset($errors['fio']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['fio'])) echo "<div class='error-msg'>{$errors['fio']}</div>"; ?>
        </div>

        <div class="form-group">
            <label>Телефон</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['phone'])) echo "<div class='error-msg'>{$errors['phone']}</div>"; ?>
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['email'])) echo "<div class='error-msg'>{$errors['email']}</div>"; ?>
        </div>

        <div class="form-group">
            <label>Дата рождения</label>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($data['birth_date'] ?? '') ?>" class="<?= isset($errors['birth_date']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['birth_date'])) echo "<div class='error-msg'>{$errors['birth_date']}</div>"; ?>
        </div>

        <div class="form-group radio-group">
            <label>Пол</label>
            <label><input type="radio" name="gender" value="male" <?= (isset($data['gender']) && $data['gender'] == 'male') ? 'checked' : '' ?>> Мужской</label>
            <label><input type="radio" name="gender" value="female" <?= (isset($data['gender']) && $data['gender'] == 'female') ? 'checked' : '' ?>> Женский</label>
            <?php if (isset($errors['gender'])) echo "<div class='error-msg'>{$errors['gender']}</div>"; ?>
        </div>

        <div class="form-group">
            <label>Любимые языки программирования (Ctrl+клик для нескольких)</label>
            <select name="languages[]" multiple class="<?= isset($errors['languages']) ? 'error-field' : '' ?>">
                <?php
                $langs = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                foreach ($langs as $lang): ?>
                    <option value="<?= $lang ?>" <?= in_array($lang, $saved_langs) ? 'selected' : '' ?>><?= $lang ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['languages'])) echo "<div class='error-msg'>{$errors['languages']}</div>"; ?>
        </div>

        <div class="form-group">
            <label>Биография</label>
            <textarea name="biography" rows="4" class="<?= isset($errors['biography']) ? 'error-field' : '' ?>"><?= htmlspecialchars($data['biography'] ?? '') ?></textarea>
            <?php if (isset($errors['biography'])) echo "<div class='error-msg'>{$errors['biography']}</div>"; ?>
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" name="contract" value="1" <?= (isset($data['contract']) && $data['contract'] == '1') ? 'checked' : '' ?> class="<?= isset($errors['contract']) ? 'error-field' : '' ?>">
            <label>С контрактом ознакомлен</label>
            <?php if (isset($errors['contract'])) echo "<div class='error-msg' style='margin-left: 10px;'>{$errors['contract']}</div>"; ?>
        </div>

        <button type="submit">Сохранить</button>
    </form>
</div>
</body>
</html>
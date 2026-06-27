<?php
header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения к БД
$db_user = 'u82291';
$db_pass = '7595792'; // ВПИШИТЕ СЮДА ВАШ ПАРОЛЬ
$db_name = 'u82291';

try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $languages = $_POST['languages'] ?? [];
    $biography = trim($_POST['biography'] ?? '');
    $contract = isset($_POST['contract']) ? '1' : '0';

    $errors = [];

    // 1. ФИО (только буквы, пробелы, дефисы)
    if (empty($fio)) {
        $errors['fio'] = 'ФИО обязательно для заполнения';
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $fio)) {
        $errors['fio'] = 'ФИО может содержать только буквы, пробелы и дефисы';
    } elseif (strlen($fio) > 150) {
        $errors['fio'] = 'ФИО не должно превышать 150 символов';
    }

    // 2. Телефон
    if (empty($phone)) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^[\+\d\s\(\)\-]{10,20}$/', $phone)) {
        $errors['phone'] = 'Телефон должен содержать только цифры, скобки, дефисы и плюс (от 10 до 20 символов)';
    }

    // 3. Email (регулярное выражение)
    if (empty($email)) {
        $errors['email'] = 'E-mail обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors['email'] = 'E-mail должен содержать символ @ и домен (например, name@domain.com)';
    }

    // 4. Дата рождения
    if (empty($birth_date)) {
        $errors['birth_date'] = 'Дата рождения обязательна';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date)) {
        $errors['birth_date'] = 'Дата должна быть в формате ГГГГ-ММ-ДД';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $birth_date);
        if (!$date || $date->format('Y-m-d') !== $birth_date) {
            $errors['birth_date'] = 'Некорректная дата';
        } else {
            $age = date_diff($date, new DateTime())->y;
            if ($age < 18 || $age > 120) {
                $errors['birth_date'] = 'Возраст должен быть от 18 до 120 лет';
            }
        }
    }

    // 5. Пол
    if (empty($gender) || !in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Необходимо выбрать пол';
    }

    // 6. Языки программирования
    $valid_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    } else {
        foreach ($languages as $lang) {
            if (!in_array($lang, $valid_languages)) {
                $errors['languages'] = 'Выбран недопустимый язык программирования';
                break;
            }
        }
    }

    // 7. Биография
    if (strlen($biography) > 5000) {
        $errors['biography'] = 'Биография не должна превышать 5000 символов';
    }

    // 8. Контракт
    if ($contract !== '1') {
        $errors['contract'] = 'Необходимо подтвердить ознакомление с контрактом';
    }

    // === СОХРАНЕНИЕ В COOKIES ===
    $data = [
        'fio' => $fio, 'phone' => $phone, 'email' => $email, 
        'birth_date' => $birth_date, 'gender' => $gender, 
        'biography' => $biography, 'contract' => $contract,
        'languages' => implode(',', $languages) // Сохраняем массив как строку
    ];

    // Сохраняем данные на 1 год (31536000 секунд)
    foreach ($data as $key => $value) {
        setcookie('data_' . $key, $value, time() + 31536000, '/');
    }

    if (!empty($errors)) {
        // Если есть ошибки - сохраняем их в Cookies ДО КОНЦА СЕССИИ (время = 0)
        foreach ($errors as $key => $message) {
            setcookie('error_' . $key, $message, 0, '/');
        }
        header('Location: form.php');
        exit();
    }

    // Если ошибок нет - сохраняем в БД
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO applications_4 (full_name, phone, email, birth_date, gender, biography, contract_agreed) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $phone, $email, $birth_date, $gender, $biography, (int)$contract]);
        $app_id = $db->lastInsertId();

        $lang_stmt = $db->prepare("SELECT id FROM programming_languages WHERE name = ?");
        $insert_lang = $db->prepare("INSERT INTO application_languages_4 (application_id, language_id) VALUES (?, ?)");
        
        foreach ($languages as $lang_name) {
            $lang_stmt->execute([$lang_name]);
            $lang = $lang_stmt->fetch();
            if ($lang) $insert_lang->execute([$app_id, $lang['id']]);
        }
        $db->commit();

        // Удаляем Cookies с ошибками (на всякий случай)
        foreach ($errors as $key => $message) {
            setcookie('error_' . $key, '', time() - 3600, '/');
        }

        header('Location: form.php?save=1');
        exit();
    } catch(PDOException $e) {
        $db->rollBack();
        die("Ошибка БД: " . $e->getMessage());
    }
}

header('Location: form.php');
exit();
?>
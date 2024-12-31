<?php
session_start();

error_reporting(E_ERROR | E_PARSE);

$users_file = "users.json";

// Функция для загрузки данных пользователей
function load_users($file) {
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true);
    }
    return [];
}

// Функция для сохранения данных пользователей
function save_users($file, $users) {
    $data = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents($file, $data);
}

// Загрузка данных пользователей
$users = load_users($users_file);

// Обработка формы регистрации
if (isset($_POST['register'])) {
    // Очистка данных от пробелов
    $name = trim($_POST['name']);
    $login = trim($_POST['login']);
    $gender = trim($_POST['gender']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Проверка на существование пользователя с таким логином
    foreach ($users as $user) {
        if ($user['login'] == $login) {
            $_SESSION["error_message_register"] = "Пользователь с таким логином уже существует.";
            header("Location: index.php");
            exit();
        }
    }

    // Проверка совпадения паролей
    if ($password !== $confirm_password) {
        $_SESSION["error_message_register"] = "Пароли не совпадают.";
        header("Location: index.php");
        exit();
    }

    // Создание нового пользователя
    $new_user = [
        'id' => count($users) + 1,
        'login' => $login,
        'gender' => $gender,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ];
    $users[] = $new_user;
    save_users($users_file, $users);

    $_SESSION["success_message"] = "Регистрация прошла успешно. Теперь вы можете войти.";
    header("Location: index.php");
    exit();
}

// Обработка формы авторизации
if (isset($_POST['login'])) {
    $login_existing = trim($_POST['login_existing']);
    $password_existing = trim($_POST['password_existing']);

    // Поиск пользователя с введенным логином
    foreach ($users as $user) {
        if ($user['login'] == $login_existing && password_verify($password_existing, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: profile.php");
            exit();
        }
    }

    $_SESSION["error_message_login"] = "Неверный логин или пароль.";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать!</title>
    <link rel="stylesheet" href="style.css"> <!-- Подключение CSS-файла -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        header {
            max-width: 100%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            box-sizing: border-box;
            margin: 0 auto 50px;
            text-align: center; /* центрируем текст внутри header */
        }
        .site-title { 
            text-align: center; 
        } 
        
        .site-title h1 { 
            float: left; /* Выровнять заголовок h1 слева */ 
            margin-right: 20px; /* Добавляем отступ между h1 и остальными элементами */ 
        } 
        
        .site-title h2, .site-title h3 { 
            display: inline-block; /* Размещаем h2 и h3 рядом */ 
            vertical-align: middle; /* Выравнивание по вертикали */ 
            margin-right: 20px; /* Добавляем отступ между h1 и остальными элементами */ 
        }

        .preview-image {
            margin-left: 15%;
            border-radius: 100%;
        }

        .floating-element {
            float: right; /* Выровнять заголовок h1 слева */ 
            margin-right: 15%; /* Добавляем отступ между h1 и остальными элементами */
        }

        main {
            flex: 1;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }

        footer {
            max-width: 100%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            box-sizing: border-box;
            margin: 50px auto 0;
            text-align: center; /* центрируем текст внутри footer */ 

            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10;
        }

        .container {
            min-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            box-sizing: border-box;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color:rgb(73, 115, 255);
            color: #fff;
            border: none;
            padding: 12px 20px;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: rgb(52, 79, 170);
        }

        .error-message {
            color: red;
            margin-top: 5px;
        }

        .success-message {
            color: green;
            margin-top: 5px;
        }

        .toggle-link {
            cursor: pointer;
            margin-bottom: 10px;
            color: rgb(73, 115, 255);
            font-size: 18px;
        }

        .user-count {
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }

        .about-section {
            margin-top: 50px;
            text-align: center;
        }

        .about-section h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }

        .about-section video {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
		
		.container-site-info {
			max-width: 1000px;
			margin: 0 auto;
			padding: 20px;
			box-sizing: border-box;
			background-color: #fff; /* Белый фон */
			border-radius: 8px; /* Для скругления углов */
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Тень */
		}

    </style>
	
    <header>
            <div class="site-title" align="center">
                <h1>Whisper🎄</h1>
                <h2>Свободная социальная сеть с открытым исходным кодом</h3>
            </div>
    </header>
	
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toggleLinks = document.querySelectorAll(".toggle-link");
            toggleLinks.forEach(function(toggleLink) {
                toggleLink.addEventListener("click", function() {
                    var formId = this.dataset.form;
                    var form = document.getElementById(formId);
                    form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
                });
            });

            // Функция для удаления пробелов в реальном времени
            function removeSpaces(input) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\s+/g, '');
                });
            }

            // Применяем функцию к нужным полям
            var inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
            inputs.forEach(function(input) {
                removeSpaces(input);
            });
        });
    </script>
</head>

<body>

    <main>

        <img src="default.png" alt="Описание изображения" class='preview-image' width='300px'>

        <div class="floating-element">
            <div class="container">
                <h1 class="toggle-link" data-form="register-form">Регистрация</h1>
                <?php
                if (isset($_SESSION["error_message_register"])) {
                    echo "<p class='error-message'>{$_SESSION["error_message_register"]}</p>";
                    unset($_SESSION["error_message_register"]);
                }
                if (isset($_SESSION["success_message"])) {
                    echo "<p class='success-message'>{$_SESSION["success_message"]}</p>";
                    unset($_SESSION["success_message"]);
                }
                ?>
                <!-- Форма регистрации -->
                <div class="form-container" id="register-form">
                    <form action="auth.php" method="POST">
                        <label for="login">Логин:</label><br>
                        <input type="text" id="login" name="login" required><br>

                        <label for="gender">Гендер:</label><br>
                        <select id="gender" name="gender" required>
                            <option value="Мужской">Мужской</option>
                            <option value="Женский">Женский</option>
                            <option value="Другой">Другой</option>
                            <option value="Предпочитаю не указывать">Предпочитаю не указывать</option>
                        </select><br>

                        <label for="email">Email:</label><br>
                        <input type="email" id="email" name="email" required><br>

                        <label for="password">Пароль:</label><br>
                        <input type="password" id="password" name="password" required><br>

                        <label for="confirm_password">Подтверждение пароля:</label><br>
                        <input type="password" id="confirm_password" name="confirm_password" required><br>

                        <label for="agree_rules">Я соглашаюсь с правилами сайта</label>
                        <input type="checkbox" id="agree_rules" name="agree_rules" required><br>

                        <input type="submit" name="register" value="Зарегистрироваться">
                    </form>
					
					<br>
					<a href="auth.php">Уже есть аккаунт?</a>
                </div>
            </div>

        </div>
    </main>
	
	<footer>
		<div class="user-count" align="center">
			<?php
				$user_count = count($users);
				echo "На нашем сайте зарегистрировано: $user_count пользователей!";
			?>
		</div>
	</footer>
</body>
</html>
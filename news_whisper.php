<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Загрузка данных из JSON файлов
$users = json_decode(file_get_contents("users.json"), true);
$posts = json_decode(file_get_contents("posts.json"), true);

// Получение информации о текущем пользователе
$current_user_id = $_SESSION["user_id"];
$current_user_info = null;
foreach ($users as $user) {
    if ($user["id"] === $current_user_id) {
        $current_user_info = $user;
        break;
    }
}

// Функция для получения друзей пользователя
function get_user_friends($user_id, $users) {
    $friends = [];
    foreach ($users as $user) {
        if (isset($user["friends"]) && is_array($user["friends"]) && in_array($user_id, $user["friends"])) {
            $friends[] = $user["id"];
        }
    }
    return $friends;
}

// Получение списка друзей текущего пользователя
$user_friends = get_user_friends($current_user_id, $users);

// Функция для проверки, является ли пост новым (сегодняшним)
function is_post_new($post) {
    return date("Y-m-d", strtotime($post["timestamp"])) === date("Y-m-d");
}

// Получение новых постов от друзей
$new_posts = [];
foreach ($posts as $post) {
    if (in_array($post["user_id"], $user_friends) && is_post_new($post)) {
        $new_posts[] = $post;
    }
}

// Сортировка постов по времени (новые в начале)
usort($new_posts, function($a, $b) {
    return strtotime($b["timestamp"]) - strtotime($a["timestamp"]);
});
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости - Whisper</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Новости <div class="text"> (Whisper) </div></h1>
		<button class="command-btn" onclick="window.location.href='news.php'">Мои друзья</button>
		<button class="command-btn" onclick="window.location.href='news_whisper.php'">Whisper</button>
        
        <p>Новостей пока нет</p>

    </div>
</body>
</html>

<!-- Блок для команд -->
<div class="commands-container">    
    <button class="command-btn" onclick="window.location.href='profile.php'">Мой профиль</button>
    <button class="command-btn" onclick="window.location.href='news.php'">Новости</button>
    <button class="command-btn" onclick="window.location.href='messages.php'">Чаты</button>
    <button class="command-btn" onclick="window.location.href='friends.php'">Друзья</button>
    <button class="command-btn" onclick="window.location.href='auth.php'">Выйти</button>
    <!-- Добавьте дополнительные кнопки по необходимости -->    
</div>
		
	<style>
		/* Reset default styles */
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: Arial, sans-serif;
			background-color: #f0f0f0;
		}

		.container {
			max-width: 800px;
			margin: 20px auto;
			background-color: #fff;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		h1 {
			font-size: 24px;
			margin-bottom: 20px;
		}

		.profile-settings {
			border: 1px solid #ccc;
			padding: 10px;
			margin-bottom: 20px;
		}

		.profile-settings h2 {
			cursor: pointer;
		}

		.profile-settings-content {
			display: none;
			padding: 10px 0;
		}

		.profile-settings-content.show {
			display: block;
		}

		.profile-info {
			display: flex;
			align-items: center;
			margin-bottom: 20px;
		}

		.profile-avatar img {
			width: 80px;
			height: 80px;
			border-radius: 50%;
			margin-right: 20px;
		}

		.profile-details h2 {
			font-size: 20px;
			margin-bottom: 5px;
		}

		.user-gender {
			font-size: 16px;
			color: #888;
			margin-left: 10px;
		}

		.post-section {
			margin-bottom: 20px;
		}

		.post-item {
			border: 1px solid #ccc;
			padding: 10px;
			margin-bottom: 10px;
		}

		.post-item .author-name {
			font-weight: bold;
		}

		.post-item .post-date {
			color: #888;
			font-size: 14px;
		}

		.post-item p {
			margin: 10px 0;
		}

		.post-item .post-image {
			max-width: 100%;
			height: auto;
			margin-top: 10px;
		}

		.all-images h2 {
			margin-bottom: 10px;
		}

		.all-images .image-list {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}

		.all-images .image-list img {
			max-width: calc(25% - 10px);
			height: auto;
			border-radius: 5px;
			border: 1px solid #ccc;
			margin-bottom: 10px;
		}
		
		.commands-container {
			display: flex;
			justify-content: center; /* Выравнивание по центру по горизонтали */
			align-items: center;     /* Выравнивание по центру по вертикали (если нужно) */
			padding: 20px;          /* Отступ от содержимого */
			border: 1px solid #ddd; /* Рамка */
			border-radius: 10px;     /* Скругление углов */
			background-color: #f9f9f9; /* Цвет фона */
			margin: 20px auto;      /* Отступы снаружи и центрирование по горизонтали */
			width: fit-content;    /* Адаптируем ширину под контент */
			gap: 15px; /* Расстояние между кнопками */
		}

		.command-btn {
			margin-bottom: 10px; /* Отступ между кнопками */
			padding: 10px 20px;
			background-color:rgb(73, 115, 255);
			color: white;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			transition: background-color 0.3s ease; /* Плавный переход при наведении */
		}

		.command-btn:hover {
			background-color: rgb(52, 79, 170);
		}
		
		.text {
			color: #888;
			font-size: 14px;
		}
	</style>
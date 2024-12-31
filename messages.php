<?php

    session_start();

    // Проверяем, авторизован ли пользователь
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }

    $userId = $_SESSION['user_id'];
    $usersFile = 'users.json';
    $chatsFile = 'chats.json';

    // Загружаем всех пользователей и данные о чатах
    $users = json_decode(file_get_contents($usersFile), true);
    $chats = json_decode(file_get_contents($chatsFile), true);

    // Функция для получения последнего сообщения с пользователем
    function getLastMessageWithUser($chats, $userId, $otherUserId) {
        $messages = $chats[$userId][$otherUserId] ?? [];
        if (!empty($messages)) {
            $lastMessage = end($messages);
            return $lastMessage['message'];
        }
        return null;
    }

?>

<style>

    .commands-container2 {
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

    .command-btn2 {
        margin-bottom: 10px; /* Отступ между кнопками */
        padding: 10px 20px;
        background-color:rgb(73, 115, 255);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease; /* Плавный переход при наведении */
    }

    .command-btn2:hover {
        background-color: rgb(52, 79, 170);
    }

    .chat-list {
        list-style-type: none; /* Убираем маркеры списка */
        padding: 0; /* Убираем отступы внутри списка */
        margin: 0; /* Убираем отступы снаружи списка */
    }

    .chat-list li {
        background-color: #f9f9f9; /* Цвет фона каждого элемента списка */
        padding: 10px; /* Внутренние отступы для каждого элемента списка */
        margin-bottom: 5px; /* Отступ между элементами списка */
        border-radius: 8px; /* Закругление углов для элементов списка */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Тень для элементов списка */
    }

    .add-friend-btn {
        padding: 10px 20px; /* Размеры кнопки */
        background-color:rgb(73, 115, 255);
        color: white; /* Цвет текста кнопки */
        border: none; /* Убираем границу кнопки */
        border-radius: 5px; /* Закругляем углы кнопки */
        cursor: pointer; /* Изменение курсора при наведении на кнопку */
        transition: background-color 0.3s ease; /* Плавный переход цвета фона при наведении */
    }

    .add-friend-btn:hover {
        background-color: rgb(52, 79, 170);
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

</style>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Чаты</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
			<h1>Чаты</h1>
			<ul class="chat-list">
				<?php foreach ($users as $user): ?>
					<?php if ($user['id'] != $userId): ?>
						<?php $lastMessage = getLastMessageWithUser($chats, $userId, $user['id']); ?>
						<?php if ($lastMessage): ?>
							<li>
								Чат с <?= htmlspecialchars($user['login']) ?>
									<table width="100%">
										<tr>
											<td align="right">
												<button class="add-friend-btn" onclick="window.location.href='currentСhat.php?user_id=<?= $user['id'] ?>'">Отправить сообщение</button>
											</td>
										</tr>
									</table>
								<p>Последнее сообщение: <?= htmlspecialchars($lastMessage) ?></p>
                                <br>
							</li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
	</div>

<!-- Блок для команд -->
<div class="commands-container2">    
    <button class="command-btn2" onclick="window.location.href='profile.php'">Мой профиль</button>
    <button class="command-btn2" onclick="window.location.href='news.php'">Новости</button>
    <button class="command-btn2" onclick="window.location.href='messages.php'">Чаты</button>
    <button class="command-btn2" onclick="window.location.href='friends.php'">Друзья</button>
    <button class="command-btn2" onclick="window.location.href='auth.php'">Выйти</button>
    <!-- Добавьте дополнительные кнопки по необходимости -->    
</div>
</body>
</html>
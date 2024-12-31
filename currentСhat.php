<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Получаем ID пользователя, с которым идет чат
if (isset($_GET['user_id'])) {
    $chatUserId = $_GET['user_id'];
} else {
    header('Location: friends.php'); // Перенаправляем на страницу друзей, если не указан ID пользователя
    exit();
}

$usersFile = 'users.json';
$chatsFile = 'chats.json';

// Загружаем всех пользователей и данные чатов
$users = json_decode(file_get_contents($usersFile), true);
$chats = json_decode(file_get_contents($chatsFile), true);

// Находим информацию о выбранном пользователе
$chatUser = null;
foreach ($users as $user) {
    if ($user['id'] == $chatUserId) {
        $chatUser = $user;
        break;
    }
}

if (!$chatUser) {
    header('Location: friends.php'); // Перенаправляем на страницу друзей, если пользователь не найден
    exit();
}

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $senderId = $_SESSION['user_id'];
        $recipientId = $chatUser['id'];
        $timestamp = date('Y-m-d H:i:s');
        
        // Создаем уникальный ID для сообщения
        $messageId = uniqid();
        
        // Добавляем сообщение в чаты для обоих пользователей
        if (!isset($chats[$senderId])) {
            $chats[$senderId] = [];
        }
        if (!isset($chats[$recipientId])) {
            $chats[$recipientId] = [];
        }
        if (!isset($chats[$senderId][$recipientId])) {
            $chats[$senderId][$recipientId] = [];
        }
        if (!isset($chats[$recipientId][$senderId])) {
            $chats[$recipientId][$senderId] = [];
        }

        $chats[$senderId][$recipientId][] = [
            'id' => $messageId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $message,
            'timestamp' => $timestamp,
        ];

        $chats[$recipientId][$senderId][] = [
            'id' => $messageId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $message,
            'timestamp' => $timestamp,
        ];
        
        // Сохраняем изменения в файл
        file_put_contents($chatsFile, json_encode($chats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    // Перенаправление пользователя после успешной отправки сообщения
    ?>
    <script>
        window.location.href = window.location.href; // Перезагружаем страницу после отправки сообщения
    </script>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Чат с <?= htmlspecialchars($chatUser['login']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<style>

    .commands-container {
        position: fixed;
        bottom: 20px; /* Отступ от нижнего края экрана */
        left: 20px; /* Отступ от левого края экрана */
        display: flex;
        flex-direction: column;
    }

    button {
		margin-bottom: 10px; /* Отступ между кнопками */
		padding: 10px 20px;
		background-color: rgb(73, 115, 255); /* Цвет фона кнопки */
		color: white;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		transition: background-color 0.3s ease; /* Плавный переход при наведении */
	}

	button:hover {
		background-color: rgb(52, 79, 170); /* Цвет фона кнопки при наведении */
	}

    /* Стили для контейнера сообщений */
    .chat-container {
        width: 100%;
    }

    /* Стили для сообщений текущего пользователя */
    .my-message {
        text-align: right;
    }

    /* Стили для сообщений других пользователей */
    .other-user-message {
        text-align: left;
    }

    /* Стили для времени сообщений справа */
    .timestamp-right {
        float: right;
        color: #999; /* Цвет для времени сообщений */
        margin-left: 10px;
    }

    /* Стили для времени сообщений слева */
    .timestamp-left {
        float: left;
        color: #999; /* Цвет для времени сообщений */
        margin-right: 10px;
    }

    /* Стили для контейнера каждого сообщения */
    .message-container {
        margin-bottom: 10px;
        overflow: auto; /* Разрешает перенос дат в случае слишком длинных сообщений */
    }

</style>

<div class="container">
    <h1>Чат с <?= htmlspecialchars($chatUser['login']) ?></h1>
    <!-- Здесь будет отображаться чат -->
    <div class="chat-container">
        <?php
        // Отображаем сообщения чата для текущего пользователя и выбранного пользователя
        if (isset($chats[$_SESSION['user_id']][$chatUser['id']])) {
            $lastDisplayedMessageId = null; // Инициализируем переменную для хранения ID последнего отображенного сообщения
            foreach ($chats[$_SESSION['user_id']][$chatUser['id']] as $message) {
                // Проверяем, что сообщение не было отображено ранее
                if ($message['id'] !== $lastDisplayedMessageId) {
                    $isMyMessage = $message['sender_id'] == $_SESSION['user_id'];
                    $messageClass = $isMyMessage ? 'my-message' : 'other-user-message';
                    $timestampClass = $isMyMessage ? 'timestamp-right' : 'timestamp-left';
                    
                    echo '<div class="message-container">';
                    echo '<span class="message-timestamp ' . $timestampClass . '">' . $message['timestamp'] . '</span>';
                    echo '<br><br>';
                    echo '<p class="' . $messageClass . '"><strong>' . htmlspecialchars($message['sender_id'] == $_SESSION['user_id'] ? 'Вы' : $chatUser['login']) . '</strong>: ' . htmlspecialchars($message['message']) . '</p>';
                    echo '</div><br>';
                    $lastDisplayedMessageId = $message['id']; // Обновляем ID последнего отображенного сообщения
                }
            }
        }
        ?>
    </div>

    <!-- Форма для отправки сообщения -->
    <form method="post">
        <textarea name="message" placeholder="Введите сообщение" required style="
            width: 50%;
            min-height: 10px;
            padding: 15px;
            font-size: 16px;
            line-height: 1.5;
            border: 2px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            resize: none;
            font-family: 'Arial', sans-serif;
            color: #333;
            outline: none;
            box-sizing: border-box;
          "
          onfocus="this.style.borderColor='#4caf50'; this.style.boxShadow='0 0 5px rgba(76, 175, 80, 0.5)'; " 
          onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"></textarea>
        <button type="submit">Отправить</button>
    </form>
</div>

<!-- Блок для команд -->
<div class="commands-container">    
    <button class="command-btn" onclick="window.location.href='profile.php'">Мой профиль</button>
    <button class="command-btn" onclick="window.location.href='news.php'">Новости</button>
    <button class="command-btn" onclick="window.location.href='messages.php'">Чаты</button>
    <button class="command-btn" onclick="window.location.href='friends.php'">Друзья</button>
    <button class="command-btn" onclick="window.location.href='auth.php'">Выйти</button>
    <!-- Добавьте дополнительные кнопки по необходимости -->    
</div>

</body>
</html>

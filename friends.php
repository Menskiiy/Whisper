	<?php
	session_start();

	// Проверяем, авторизован ли пользователь
	if (!isset($_SESSION['user_id'])) {
		header('Location: index.php');
		exit();
	}

	$userId = $_SESSION['user_id'];
	$usersFile = 'users.json';

	// Загружаем всех пользователей
	$users = json_decode(file_get_contents($usersFile), true);

	// Найти текущего пользователя
	$currentUser = null;
	foreach ($users as &$user) {
		if ($user['id'] == $userId) {
			$currentUser = &$user;
			break;
		}
	}

	// Проверить наличие массива друзей у текущего пользователя
	if (!isset($currentUser['friends'])) {
		$currentUser['friends'] = [];
	}

	$friends = $currentUser['friends'];

	// Обработка добавления в друзья
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_friend'])) {
		$friendId = $_POST['add_friend'];
		if (!in_array($friendId, $friends)) {
			// Проверяем, что друг с таким ID существует
			$friendExists = false;
			foreach ($users as &$user) {
				if ($user['id'] == $friendId) {
					$friendExists = true;
					break;
				}
			}

			if ($friendExists) {
				// Добавляем друга в список друзей текущего пользователя
				$friends[] = $friendId;
				$currentUser['friends'] = $friends;
				
				// Добавляем текущего пользователя в список друзей друга
				foreach ($users as &$user) {
					if ($user['id'] == $friendId) {
						if (!isset($user['friends'])) {
							$user['friends'] = [];
						}
						$user['friends'][] = $userId;
						break;
					}
				}
				
				file_put_contents($usersFile, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
			}
		}
		header("Location: friends.php");
		exit;
	}

	// Обработка удаления из друзей
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_friend'])) {
		$friendId = $_POST['remove_friend'];
		if (in_array($friendId, $friends)) {
			$key = array_search($friendId, $friends);
			unset($friends[$key]);
			$currentUser['friends'] = array_values($friends); // Перезаписываем массив без удаленного друга
			
			// Удаляем текущего пользователя из списка друзей друга
			foreach ($users as &$user) {
				if ($user['id'] == $friendId) {
					$friendKey = array_search($userId, $user['friends']);
					unset($user['friends'][$friendKey]);
					$user['friends'] = array_values($user['friends']); // Перезаписываем массив без удаленного друга
					break;
				}
			}
			
			file_put_contents($usersFile, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}
		header("Location: friends.php");
		exit;
	}


	// Поиск друзей
	$searchResult = [];
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
		$search = trim($_POST['search']);
		foreach ($users as $user) {
			if (strpos($user['login'], $search) !== false && $user['id'] != $userId) {
				$searchResult[] = $user;
			}
		}
	}

	// Фильтруем всех пользователей, исключая текущего и друзей
	$allUsers = array_filter($users, function($user) use ($userId, $friends) {
		return $user['id'] != $userId && !in_array($user['id'], $friends);
	});

	$perPage = 10;
	$totalPages = ceil(count($allUsers) / $perPage);
	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$page = max(1, min($totalPages, $page));
	$offset = ($page - 1) * $perPage;
	$usersToShow = array_slice($allUsers, $offset, $perPage);

	?>
	<!DOCTYPE html>
	<html lang="ru">
	<head>
		<meta charset="UTF-8">
		<title>Страница друзей</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="container">
			<h1>Поиск друзей</h1>
			<form method="post">
				
				<input type="text" name="search" placeholder="Введите имя..." required
				
				style="
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
          onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
		  >
				<button type="submit">Найти</button>
			</form>
			<div class="search-results">
				<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])): ?>
				<?php if (count($searchResult) > 0): ?>
					<ul>
					<br>
					<?php foreach ($searchResult as $user): ?>
						<li>
						<?= htmlspecialchars($user['login']) ?>
						<form method="post" style="display:inline;">
							<input type="hidden" name="<?php echo in_array($user['id'], $friends) ? 'remove_friend' : 'add_friend'; ?>" value="<?= $user['id'] ?>">
							<button type="submit" class="friend-action-btn"><?php echo in_array($user['id'], $friends) ? 'Удалить из друзей' : 'Добавить в друзья'; ?></button>
						</form>
						<button class="add-friend-btn" onclick="window.location.href='profileOtherUser.php?user_id=<?= $user['id'] ?>'">Зайти в профиль пользователя</button>
						<button class="add-friend-btn" onclick="window.location.href='currentСhat.php?user_id=<?= $user['id'] ?>'">Отправить сообщение</button>
						</li>
					<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<br>
					<p>Нет пользователей с таким именем.</p>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="container">
			<h1>Мои друзья</h1>
			<ul class="friends-list">
				<?php if (count($friends) > 0): ?>
					<?php foreach ($friends as $friendId): ?>
						<?php foreach ($users as $user): ?>
							<?php if ($user['id'] == $friendId): ?>
								<li>
									<?= htmlspecialchars($user['login']) ?>
									<form method="post" style="display:inline;">
										<input type="hidden" name="remove_friend" value="<?= $friendId ?>">
										<button type="submit" class="friend-action-btn">Удалить из друзей</button>
									</form>
									<button class="add-friend-btn" onclick="window.location.href='profileOtherUser.php?user_id=<?= $user['id'] ?>'">Зайти в профиль пользователя</button>
									<button class="add-friend-btn" onclick="window.location.href='currentСhat.php?user_id=<?= $user['id'] ?>'">Отправить сообщение</button> <!-- Новая кнопка -->
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php else: ?>
					<p>У вас нет друзей.</p>
				<?php endif; ?>
			</ul>
		</div>

		<div class="container">
			<h1>Все пользователи</h1>
			<ul class="users-list">
			
				<ul>
					<?php foreach ($usersToShow as $user): ?>
						<li>
							<?= htmlspecialchars($user['login']) ?>
							<form method="post" style="display:inline;">
								<input type="hidden" name="add_friend" value="<?= $user['id'] ?>">
								<button type="submit" class="add-friend-btn">Добавить в друзья</button>
							</form>
							<button class="add-friend-btn" onclick="window.location.href='profileOtherUser.php?user_id=<?= $user['id'] ?>'">Зайти в профиль пользователя</button>
							<button class="add-friend-btn" onclick="window.location.href='currentСhat.php?user_id=<?= $user['id'] ?>'">Отправить сообщение</button> <!-- Новая кнопка -->
						</li>
					<?php endforeach; ?>
				</ul>
				
			<?php if ($page < $totalPages): ?>
				<a href="?page=<?= $page + 1 ?>">Следующая страница</a>
			<?php endif; ?>
			
			</ul>
		</div>

		<style>
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
			
			li {
				list-style-type: none; /* Убираем маркеры */
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
			
			/* Стили для кнопок ВСЕ ДРУЗЬЯ */
			
			.friend-action-btn {
				padding: 5px 10px;
				background-color: #007bff; /* Цвет фона кнопки */
				color: white;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				transition: background-color 0.3s ease; /* Плавный переход при наведении */
			}

			.friend-action-btn:hover {
				background-color: #0056b3; /* Цвет фона кнопки при наведении */
			}

			/* Стили для списка друзей */
			.friends-list {
				list-style-type: none;
				padding: 0;
			}

			.friends-list li {
				background-color: #f9f9f9; /* Цвет фона каждого элемента списка */
				padding: 10px; /* Внутренние отступы для каждого элемента списка */
				margin-bottom: 20px; /* Отступ между элементами списка */
				border-radius: 8px; /* Закругление углов для элементов списка */
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Тень для элементов списка */
			}

			.friends-list li:last-child {
				margin-bottom: 0; /* Убираем отступ для последнего элемента списка */
			}
			
			/* Стили для кнопок ВСЕ ПОЛЬЗОВАТЕЛИ */
			
			.add-friend-btn {
				padding: 5px 10px;
				background-color: #007bff; /* Цвет фона кнопки */
				color: white;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				transition: background-color 0.3s ease; /* Плавный переход при наведении */
			}

			.add-friend-btn:hover {
				background-color: #0056b3; /* Цвет фона кнопки при наведении */
			}

			.users-list {
				list-style-type: none;
				padding: 0;
			}

			.users-list li {
				background-color: #f9f9f9; /* Цвет фона каждого элемента списка */
				padding: 10px; /* Внутренние отступы для каждого элемента списка */
				margin-bottom: 20px; /* Отступ между элементами списка */
				border-radius: 8px; /* Закругление углов для элементов списка */
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Тень для элементов списка */
			}

			.users-list li:last-child {
				margin-bottom: 0; /* Убираем отступ для последнего элемента списка */
			}	
		</style>

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

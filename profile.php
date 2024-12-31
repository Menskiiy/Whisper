<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="style.css"> <!-- Подключение CSS-файла -->
	
	<style>
		
        /* Дополнительные стили для изображений в постах */
        .post-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .post-images img {
            max-width: 80px;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

		.all-images .image-list {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}
		
		.all-images .image-list img {
			max-width: calc(25% - 10px); /* Вычисляем ширину каждого изображения с учетом промежутка между ними */
			height: auto;
			border-radius: 5px;
			border: 1px solid #ccc;
			margin-bottom: 10px; /* Добавляем отступ снизу каждого изображения */
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
            max-width: 80px;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
		
		.all {
			position: fixed;
			top: 0;
			left: 0;
		}
		
        .profile-settings {
            padding: 10px;
            margin-bottom: 20px;
			Text-align: center	
        }

        .profile-settings h2 {
            cursor: pointer;
        }

        .profile-settings-content {
            display: none;
        }

        .profile-settings-content.show {
            display: block;
        }

        /* Стиль для отображения гендера пользователя */
        .user-gender {
            font-size: 16px;
            color: #888;
            margin-left: 10px;
        }
		
		body {
			font-family: Arial, sans-serif;
			background-color: #f0f0f0; /* Цвет фона страницы */
			background-image: url('background_site.gif'); /* Фоновое изображение */
			background-size: cover; /* Полное покрытие фоном */
			background-position: center; /* Выравнивание фона по центру */
			background-repeat: no-repeat; /* Запрещаем повторение фона */
			background-attachment: fixed; /* Фиксированный фон */
			min-height: 100vh; /* Устанавливаем минимальную высоту страницы как 100% высоты окна */
			overflow-x: hidden; /* Запрещаем горизонтальный скроллинг */
			margin: 0; /* Убираем отступы по умолчанию */
			padding: 0; /* Убираем отступы по умолчанию */
		}
		
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
			margin-right: 10px;
		}

		button:hover {
			background-color: rgb(52, 79, 170); /* Цвет фона кнопки при наведении */
		}
		
		.post-item {
			border: 1px solid #ccc;
			padding: 10px;
			margin-bottom: 10px;
		}

    </style>
</head>
<body>
    <div class="container">
        <h1>Профиль пользователя</h1>

        <?php
        session_start();

        // Функция для получения информации о пользователе по его ID
		function getUserInfoById($user_id) {
			$users_json = file_get_contents('users.json'); // Получаем содержимое файла users.json
			$users_data = json_decode($users_json, true); // Декодируем JSON в массив
			foreach ($users_data as $user) {
				if ($user['id'] == $user_id) {
					return $user; // Возвращаем информацию о пользователе
				}
			}
			return null; // Если пользователь не найден, возвращаем null или другое значение
		}

		// Получаем ID текущего пользователя из сессии
		$current_user_id = $_SESSION['user_id'];

		// Получаем информацию о текущем пользователе по его ID
		$current_user_info = getUserInfoById($current_user_id);

		// Проверяем, что информация о пользователе получена и не пуста
		if ($current_user_info && isset($current_user_info['id'])) {
			$user_id = $current_user_info['id']; // Получаем ID пользователя из информации о пользователе
			$username = $current_user_info['login']; // Получаем имя пользователя из информации о пользователе
			$post["author"] = $username; // Устанавливаем имя пользователя как автора поста
		} else {
			// Обработка ситуации, когда информация о пользователе не найдена
			// Можно вывести сообщение об ошибке или выполнить другие действия
		}

        // Считываем пользователей из файла users.json
        $current_user = json_decode(file_get_contents("users.json"), true);

        // Найти текущего пользователя в массиве пользователей
        $current_user_data = null;
        foreach ($current_user as &$user) {
            if ($user['id'] == $user_id) {
                $current_user_data = &$user;
                break;
            }
        }
        unset($user);

        // Логика изменения имени пользователя
        if (isset($_POST["change_username"]) && !empty($_POST["new_username"])) {
            $new_username = $_POST["new_username"];
            $current_user_data['login'] = $new_username;
            $_SESSION['user']['login'] = $new_username;
            file_put_contents("users.json", json_encode($current_user, JSON_PRETTY_PRINT));
            
            // Обновление имени в profile.json
            $profile_data = json_decode(file_get_contents("profile.json"), true);
            $profile_data['username'] = $new_username;
            file_put_contents("profile.json", json_encode($profile_data, JSON_PRETTY_PRINT));
            header("Location: profile.php");
            exit;
        }
		
        // Логика изменения аватара
        if (isset($_POST["change_avatar"])) {
            $new_avatar = $_FILES["avatar"]["name"];
            $new_avatar_tmp = $_FILES["avatar"]["tmp_name"];
            $new_avatar_path = "uploads/user_avatar_$user_id.jpg";
            move_uploaded_file($new_avatar_tmp, $new_avatar_path);

            // Обновление пути к аватару в profile.json
            $profile_data = json_decode(file_get_contents("profile.json"), true);
            $profile_data['avatar'] = $new_avatar_path;
            file_put_contents("profile.json", json_encode($profile_data, JSON_PRETTY_PRINT));
            header("Location: profile.php");
            exit;
        }
		
		// Логика изменения гендера
		if (isset($_POST["change_gender"]) && !empty($_POST["new_gender"])) {
			$new_gender = $_POST["new_gender"];
			$current_user_data['gender'] = $new_gender;
			file_put_contents("users.json", json_encode($current_user, JSON_PRETTY_PRINT));
			header("Location: profile.php");
			exit;
		}

		// Логика изменения возраста
		if (isset($_POST["change_age"]) && !empty($_POST["new_age"])) {
			$new_age = intval($_POST["new_age"]);
			if ($new_age >= 0) {
				$current_user_data['age'] = $new_age;
				file_put_contents("users.json", json_encode($current_user, JSON_PRETTY_PRINT));
			} else {
				$error_message = "Возраст не может быть отрицательным!";
			}
		}
		
		// Логика изменения информации о себе
		if (isset($_POST["change_about_me"]) && !empty($_POST["about_me"])) {
			$about_me = $_POST["about_me"];
			// Обновляем информацию о себе для текущего пользователя в users.json
			foreach ($current_user as &$user) {
				if ($user['id'] == $user_id) {
					$user['about_me'] = $about_me;
					break;
				}
			}
			unset($user);
			// Сохраняем обновленные данные в users.json
			file_put_contents("users.json", json_encode($current_user, JSON_PRETTY_PRINT));
			// Перенаправление пользователя после сохранения
			header("Location: profile.php");
			exit;
		}
        ?>

        <!-- Настройки профиля -->
        <div class="profile-settings1">
		<div class="command-btn">
            <h2 onclick="toggleProfileSettings()">Настройки профиля</h2>
            <div class="profile-settings-content">
                <form action="profile.php" method="post" enctype="multipart/form-data">
                    <label for="avatar">Аватар:</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*">
                    <button type="submit" name="change_avatar">Изменить аватар</button>
                </form>

                <!-- Форма для изменения имени пользователя -->
                <form action="profile.php" method="post">
                    <label for="new_username">Логин:</label>
                    <input type="text" name="new_username" id="new_username">
                    <button type="submit" name="change_username">Изменить логин</button>
                </form>
				
				<!-- Форма для изменения гендера -->
				<form action="profile.php" method="post">
					<label for="new_gender">Гендер:</label>
					<select name="new_gender" id="new_gender">
						<option value="Мужской">Мужской</option>
						<option value="Женский">Женский</option>
						<option value="Другой">Другой</option>
						<option value="Предпочитаю не указывать">Предпочитаю не указывать</option>
					</select>
					<button type="submit" name="change_gender">Изменить гендер</button>
				</form>
				
				<!-- Форма для изменения возраста -->
				<form action="profile.php" method="post">
					<label for="new_age">Возраст:</label>
					<input type="number" name="new_age" id="new_age" min="0">
					<button type="submit" name="change_age">Изменить возраст</button>
					<?php
					if (isset($error_message)) {
						echo '<span class="error-message">' . $error_message . '</span>';
					}
					?>
				</form>
				
				<!-- Форма для изменения информации о себе -->
				<form action="profile.php" method="post">
					<label for="about_me">О себе:</label>
					<textarea name="about_me" id="about_me" rows="4" cols="50"><?php echo isset($about_me) ? htmlspecialchars($about_me) : ''; ?></textarea>
					<button type="submit" name="change_about_me">Сохранить информацию о себе</button>
				</form>
				
            </div>
        </div>
		</div>
		<br>

        <div class="profile-info">
		
			<div class="profile-avatar">
				<?php
				// Проверка наличия пользовательского аватара
				$avatar_path = "uploads/user_avatar_$user_id.jpg";
				if (file_exists($avatar_path)) {
					echo '<img src="' . $avatar_path . '" alt="Аватар пользователя" class="avatar-image">';
				} else {
					echo '<img src="default.png" alt="Аватар пользователя" class="avatar-image">';
				}
				?>
			</div>
			
			<div class="profile-details">
				<h2>
					<?php
					echo htmlspecialchars($post["author"]);
					?>
				</h2>
				<?php
				// Отображение гендера и возраста
				if (isset($current_user_data['gender'])) {
					echo '<span class="user-gender">'. "Гендер: " . ucfirst($current_user_data['gender']) . '</span>';
				}
				if (isset($current_user_data['age'])) {
					echo '<br>';
					echo ' <span class="user-gender">' . "Возраст: " . $current_user_data['age'] . " лет" . '</span>';
				}
				?>
			</div>
			
		</div>
		
		<?php
			echo 'О себе:';
			echo '<br>';
			echo '<br>';
		
			if (isset($current_user_data['about_me'])) {
				echo '<p>' . htmlspecialchars($current_user_data['about_me']) . '</p>';
			}
			else {
				echo 'Пользователь не оставил о себе информации';			
			}
		?>
			
</div>

	<div class="container">
        <!-- Новая секция для управления постами -->
        <div class="post-section">
            <h2>Новый пост</h2>
			<br>
            <!-- Форма для добавления поста с возможностью вставки фото -->
            <form action="profile.php" method="post" enctype="multipart/form-data">

			<textarea name="post_content" 
          placeholder="Напишите свой пост здесь..." 
          style="
            width: 100%;
            min-height: 120px;
            padding: 15px;
            font-size: 16px;
            line-height: 1.5;
            border: 2px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            resize: vertical;
            font-family: 'Arial', sans-serif;
            color: #333;
            outline: none;
            box-sizing: border-box;
          "
          onfocus="this.style.borderColor='#4caf50'; this.style.boxShadow='0 0 5px rgba(76, 175, 80, 0.5)'; " 
          onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';"
          >
</textarea>

                <input type="file" class="command-btn" name="post_image" accept="image/*">
                <button type="submit" name="submit_post">Опубликовать</button>
            </form>
			
            <!-- Список постов -->
            <?php
            // Загружаем текущие посты из файла posts.json
            $posts = [];
            if (file_exists("posts.json")) {
                $posts = json_decode(file_get_contents("posts.json"), true);
            }

        // Логика добавления поста
		if (isset($_POST["submit_post"]) && !empty($_POST["post_content"])) {
			$post_content = $_POST["post_content"];
			$post_image = $_FILES["post_image"]["name"];
			$post_image_tmp = $_FILES["post_image"]["tmp_name"];
			$new_post = [
				"id" => uniqid(),
				"content" => $post_content,
				"image" => $post_image,
				"avatar" => "user_avatar_$user_id.jpg",
				"author" => $username,
				"timestamp" => date("Y-m-d H:i:s"),
				"user_id" => $user_id
			];
			$posts[] = $new_post;
			move_uploaded_file($post_image_tmp, "uploads/" . $post_image);
			file_put_contents("posts.json", json_encode($posts, JSON_PRETTY_PRINT));

			// Перенаправление пользователя после успешного добавления поста
			if (headers_sent()) {
				echo "<script>window.location.href='profile.php';</script>";
			} else {
				header("Location: profile.php");
			}
			exit; // Обязательно завершаем выполнение скрипта после перенаправления
		}

            // Логика редактирования поста
            if (isset($_POST["edit_post"])) {
                $post_id = $_POST["post_id"];
                $edited_content = "";
                $edited_image = "";
                foreach ($posts as $post) {
                    if ($post["id"] === $post_id) {
                        $edited_content = $post["content"];
                        $edited_image = $post["image"];
                        break;
                    }
                }
            }

            // Логика обработки редактирования поста с изменением фото
            if (isset($_POST["confirm_edit"])) {
                $post_id = $_POST["post_id"];
                $edited_content = $_POST["edited_content"];
                $edited_image = $_FILES["edited_image"]["name"];
                $edited_image_tmp = $_FILES["edited_image"]["tmp_name"];
                foreach ($posts as &$post) {
                    if ($post["id"] === $post_id) {
                        $post["content"] = $edited_content;
                                                if (!empty($edited_image)) {
                            unlink("uploads/" . $post["image"]);
                            $post["image"] = $edited_image;
                            move_uploaded_file($edited_image_tmp, "uploads/" . $edited_image);
                        }
                        break;
                    }
                }
                file_put_contents("posts.json", json_encode($posts, JSON_PRETTY_PRINT));
                
				?>
				
				<script>
					window.location.href = "profile.php";
				</script>
				
				<?php
                exit;
            }

            // Логика удаления поста
            if (isset($_POST["delete_post"])) {
                $post_id = $_POST["post_id"];
                $posts = array_filter($posts, function($post) use ($post_id) {
                    if ($post["id"] === $post_id) {
                        return false;
                    }
                    return true;
                });
                file_put_contents("posts.json", json_encode($posts, JSON_PRETTY_PRINT));
            }
			?>
	</div>
	</div>
	
	<div class="container">
		<h2>Мои посты</h2>
		<div class="post-section">
			<?php 
			// Вывод списка постов только текущего пользователя
			if (!empty($posts)) {
				foreach (array_reverse($posts) as $post) {
					// Проверка, является ли текущий пользователь автором этого поста
					if ($post["user_id"] === $_SESSION["user_id"]) {
						echo '<div class="post-item">';
						echo '<div class="post-header">';
						echo '<div class="profile-avatar">';
						echo '<img src="uploads/' . $post["avatar"] . '" alt="Аватар автора" class="avatar-image">';
						echo '</div>';
						
						echo '<span class="author-name">' . htmlspecialchars($post["author"]) . '</span>';
						echo " ";
						echo '<span class="post-date">' . '<br>' . $post["timestamp"] . ' (UTC) ' . '</span>';
						echo '</div>';
						echo '<br>';
						echo '<p>' . htmlspecialchars($post["content"]) . '</p>';
						echo '<br>';
						if (!empty($post["image"])) {
							echo '<img src="uploads/' . $post["image"] . '" alt="Изображение в посте" class="post-image" style="width: 50%; height: auto;">';
						}
						if (isset($_POST["edit_post"]) && $_POST["post_id"] === $post["id"]) {
							echo '<form action="profile.php" method="post" enctype="multipart/form-data">';
							echo '<input type="hidden" name="post_id" value="' . $post["id"] . '">';
							echo '<textarea name="edited_content" placeholder="Введите новое содержимое">' . htmlspecialchars($edited_content) . '</textarea>';
							echo '<input type="file" name="edited_image" accept="image/*">';
							echo '<button type="submit" name="confirm_edit">Сохранить</button>';
							echo '</form>';
						} else {
							echo '<form action="profile.php" method="post">';
							echo '<input type="hidden" name="post_id" value="' . $post["id"] . '">';
							echo '<br>';
							echo '<button type="submit" name="edit_post">Редактировать</button>';
							echo '<button type="submit" name="delete_post">Удалить</button>';
							echo '</form>';
						}
						echo '</div>';
					}
				}
			} else {
				echo '<p>У вас пока нет постов.</p>';
			}
			?>
		</div>
	
		<!-- Блок для отображения всех изображений профиля -->
		<div class="all">
			<div class="all-images">
				<div class="container">
					<h2>Все изображения вашего профиля</h2>
					<div class="image-list">
						<?php
						// Загружаем текущие посты из файла posts.json
						$posts = [];
						if (file_exists("posts.json")) {
							$posts = json_decode(file_get_contents("posts.json"), true);
						}

						// Логика вывода всех изображений из постов текущего пользователя
						if (!empty($posts)) {
							$counter = 0;
							$groupCounter = 0;
							
							foreach ($posts as $post) {
								if (!empty($post["image"]) && $post["user_id"] === $_SESSION["user_id"]) {
									if ($groupCounter % 8 == 0) {
										if ($groupCounter > 0) {
											echo '</div>'; // Закрываем предыдущую группу
										}
										echo '<div class="image-group">'; // Открываем новую группу
									}
									echo '<div class="image-item"><img src="uploads/' . htmlspecialchars($post["image"], ENT_QUOTES, 'UTF-8') . '" alt="Изображение в посте" class="post-image"></div>';
									
									$groupCounter++;
								}
							}
							if ($groupCounter > 0) {
								echo '</div>'; // Закрываем последнюю группу
							}
						} else {
							echo '<p>У вас пока нет постов с изображениями.</p>';
						}					
						
						?>
					</div>
				</div>
			</div>
		</div>

    <script>
        function toggleProfileSettings() {
            const settingsContent = document.querySelector('.profile-settings-content');
            settingsContent.classList.toggle('show');
        }
    </script>

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
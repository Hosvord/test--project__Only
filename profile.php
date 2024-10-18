<?php
global $pdo;
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
	header('Location: index.php');
	exit;
}
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$new_name = $_POST['name'];
	$new_email = $_POST['email'];
	$new_phone = $_POST['phone'];
	$new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

	// Обновляем данные пользователя в базе данных
	$stmt = $pdo->prepare('UPDATE test SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?');
	$stmt->execute([$new_name, $new_email, $new_phone, $new_password, $_SESSION['user_id']]);

	// Обновляем данные в сессии
	$_SESSION['user_name'] = $new_name;
	$_SESSION['user_email'] = $new_email;
	$_SESSION['user_phone'] = $new_phone;

	// Сообщение об успехе
	echo "Профиль успешно обновлен!";
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Профиль</title>
</head>
<body>
<h2>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>

<p>Это ваша страница профиля.</p>

<form action="profile.php" method="POST">
	<label for="name">Имя:</label>
	<input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required><br>

	<label for="email">Электронная почта:</label>
	<input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required><br>

	<label for="phone">Телефон:</label>
	<input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($_SESSION['user_phone']); ?>" required><br>

	<label for="password">Новый пароль:</label>
	<input type="password" name="password" id="password" required><br>

	<button type="submit">Сохранить изменения</button>
</form>

<a href="logout.php">Выйти</a>
</body>
</html>

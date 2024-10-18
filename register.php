<?php
global $pdo;
require 'database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = $_POST['name'];
	$phone = $_POST['phone'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$confirm_password = $_POST['confirm_password'];

	// Проверка на ошибки
	if (empty($name) && empty($phone) && empty($email) && empty($password) && empty($confirm_password)) {
		$errors[] = "Все поля обязательны для заполнения.";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Некорректный адрес электронной почты.";
	} elseif ($password !== $confirm_password) {
		$errors[] = "Пароли не совпадают.";
	} else {
		// Проверка на существующего пользователя
		$stmt = $pdo->prepare("SELECT * FROM test WHERE email = ?");
		$stmt->execute([$email]);
		if ($stmt->fetch()) {
			$errors[] = "Пользователь с таким email уже существует.";
		} else {
			// Хешируем пароль
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);

			// Вставка нового пользователя в базу данных
			$stmt = $pdo->prepare("INSERT INTO test (name, phone, email, password) VALUES (?, ?, ?, ?)");
			$stmt->execute([$name, $phone, $email, $hashed_password]);

			// Перенаправление на страницу авторизации
			header('Location: index.php');
			exit;
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Регистрация</title>
</head>
<body>
<h2>Регистрация</h2>
<?php if ($errors): ?>
	<ul>
		<?php foreach ($errors as $error): ?>
			<li><?php echo htmlspecialchars($error); ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form action="register.php" method="POST">
	<label for="name">Имя:</label>
	<input type="text" id="name" name="name"><br>

	<label for="phone">Телефон:</label>
	<input type="text" id="phone" name="phone"><br>

	<label for="email">Эл. почта:</label>
	<input type="email" id="email" name="email"><br>

	<label for="password">Пароль:</label>
	<input type="password" id="password" name="password"><br>

	<label for="confirm_password">Повтор пароля:</label>
	<input type="password" id="confirm_password" name="confirm_password"><br>

	<button type="submit">Зарегистрироваться</button>
</form>
</body>
</html>

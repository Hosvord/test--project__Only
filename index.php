<?php
global $pdo;
require 'database.php';
require 'config.php';
session_start();

$errors = [];

function check_captcha($token)
{
	var_dump($token);
	$ch = curl_init("https://smartcaptcha.yandexcloud.net/validate");
	$args = [
		"secret" => SMARTCAPTCHA_SERVER_KEY,
		"token" => $token,
		"ip" => $_SERVER['REMOTE_ADDR'],
	];
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpcode !== 200) {
		echo "Ошибка при проверке капчи: код=$httpcode; сообщение=$server_output\n";
		return false;
	}

	$resp = json_decode($server_output);
	return $resp->status === "ok";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	print_r($_POST);

	$login = $_POST['login'] ?? '';
	$password = $_POST['password'] ?? '';
	$token = $_POST['smart-token'] ?? '';

	if (empty($login) || empty($password)) {
		$errors[] = "Заполните все поля.";
	}

	if (empty($token)) {
		$errors[] = "Токен капчи не был передан или он пустой.";
	} elseif (!check_captcha($token)) {
		$errors[] = "Проверка капчи не пройдена. Вы, возможно, робот.";
	}


	if (empty($errors)) {
		// Определяем, является ли введенное значение email или номером телефона
		$queryField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

		// Подготавливаем SQL-запрос в зависимости от типа входных данных
		$stmt = $pdo->prepare("SELECT * FROM test WHERE $queryField = ?");
		$stmt->execute([$login]);
		$user = $stmt->fetch();

		if ($user && password_verify($password, $user['password'])) {
			// Сохраняем информацию о пользователе в сессии
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_name'] = $user['name'];
			$_SESSION['user_email'] = $user['email'];
			$_SESSION['user_phone'] = $user['phone'];

			// Перенаправляем на страницу профиля
			header('Location: profile.php');
			exit;
		} else {
			$errors[] = "Неверный логин или пароль.";
		}
	}

}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Авторизация</title>
	<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
</head>
<body>
<h2>Авторизация</h2>
<?php if ($errors): ?>
	<ul>
		<?php foreach ($errors as $error): ?>
			<li><?php echo htmlspecialchars($error); ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form action="index.php" method="POST">
	<label for="login">Эл. почта или телефон:</label>
	<input type="text" id="login" name="login"><br>

	<label for="password">Пароль:</label>
	<input type="password" id="password" name="password"><br>
	<div
		id="captcha-container"
		class="smart-captcha"
		data-sitekey="<?php echo SMARTCAPTCHA_CLIENT_KEY; ?>"
	>
		<input type="hidden" name="smart-token" value="">

	</div>
	<button type="submit">Войти</button>
</form>

<p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>

</body>
</html>

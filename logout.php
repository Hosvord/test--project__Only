<?php
session_start();
session_destroy();

// Перенаправление на страницу авторизации
header('Location: index.php');
exit;

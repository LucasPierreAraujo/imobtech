<?php
setcookie('token', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict']);
header('Location: login.php');
exit;

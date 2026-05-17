<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../classes/JWT.php';

$token = $_COOKIE['token'] ?? '';
$usuario_logado = JWT::verificar($token, getenv('JWT_SECRET'));

if (!$usuario_logado) {
    header('Location: /login.php');
    exit;
}

<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../classes/JWT.php';

function autenticarApi() {
    $headers = getallheaders();
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!str_starts_with($authorization, 'Bearer ')) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token não fornecido.']);
        exit;
    }

    $token   = substr($authorization, 7);
    $usuario = JWT::verificar($token, getenv('JWT_SECRET'));

    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token inválido ou expirado.']);
        exit;
    }

    return $usuario;
}

function respostaJson($dados, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

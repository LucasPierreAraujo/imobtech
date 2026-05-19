<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../classes/JWT.php';

class Api {
    public static function autenticar(): array {
        $headers       = getallheaders();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!str_starts_with($authorization, 'Bearer ')) {
            self::responder(['erro' => 'Token não fornecido.'], 401);
        }

        $token   = substr($authorization, 7);
        $usuario = JWT::verificar($token, getenv('JWT_SECRET'));

        if (!$usuario) {
            self::responder(['erro' => 'Token inválido ou expirado.'], 401);
        }

        return $usuario;
    }

    public static function responder(array $dados, int $codigo = 200): void {
        http_response_code($codigo);
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

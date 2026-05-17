<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/classes/JWT.php';

$token = $_COOKIE['token'] ?? '';
if (JWT::verificar($token, getenv('JWT_SECRET'))) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Usuario.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->conectar();
    $usuario = new Usuario($conn);

    $usuario->usuario = $_POST['usuario'];
    $usuario->senha = $_POST['senha'];

    $dados = $usuario->login();

    if ($dados) {
        $payload = [
            'id'   => $dados['id'],
            'nome' => $dados['nome'],
            'exp'  => time() + (8 * 60 * 60)
        ];
        $jwt = JWT::gerar($payload, getenv('JWT_SECRET'));
        $seguro = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie('token', $jwt, ['expires' => $payload['exp'], 'path' => '/', 'httponly' => true, 'secure' => $seguro, 'samesite' => 'Strict']);
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Imobtech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a3c5e; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { border: none; border-radius: 10px; width: 100%; max-width: 380px; }
        .card-header { background-color: #1a3c5e; color: white; text-align: center; border-radius: 10px 10px 0 0 !important; padding: 20px; }
    </style>
</head>
<body>
<div class="card shadow">
    <div class="card-header">
        <h4 class="mb-0">Imobtech</h4>
        <small>Acesso ao sistema</small>
    </div>
    <div class="card-body p-4">
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Usuário</label>
                <input type="text" name="usuario" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        <hr>
        <p class="text-center mb-0"><a href="cadastro.php">Criar conta</a></p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

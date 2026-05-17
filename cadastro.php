<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
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

    $usuario->nome = $_POST['nome'];
    $usuario->usuario = $_POST['usuario'];
    $usuario->senha = $_POST['senha'];

    if (strlen($_POST['senha']) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($usuario->usuarioExiste()) {
        $erro = 'Este nome de usuário já está em uso.';
    } elseif ($usuario->cadastrar()) {
        header('Location: login.php?msg=Conta criada! Faça login.');
        exit;
    } else {
        $erro = 'Erro ao criar conta.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar Conta - Imobtech</title>
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
        <h4 class="mb-0">Criar Conta</h4>
        <small>Imobtech</small>
    </div>
    <div class="card-body p-4">
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Nome completo</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Usuário</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Criar conta</button>
        </form>
        <hr>
        <p class="text-center mb-0"><a href="login.php">Já tenho conta</a></p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

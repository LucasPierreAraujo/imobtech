<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imobtech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .navbar { background-color: #1a3c5e !important; }
        .navbar-brand { color: #fff !important; font-weight: bold; font-size: 1.4rem; }
        .nav-link { color: #ccc !important; }
        .nav-link:hover { color: #fff !important; }
        .card { border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #1a3c5e; border-color: #1a3c5e; }
        .btn-primary:hover { background-color: #0f2a45; }
        footer { background-color: #1a3c5e; color: #aaa; padding: 15px 0; margin-top: 50px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Imobtech</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="../imoveis/index.php">Imóveis</a></li>
                <li class="nav-item"><a class="nav-link" href="../clientes/index.php">Clientes</a></li>
                <li class="nav-item"><a class="nav-link" href="../contratos/index.php">Contratos</a></li>
                <li class="nav-item"><span class="nav-link text-warning">Olá, <?= htmlspecialchars($usuario_logado['nome'] ?? '') ?></span></li>
                <li class="nav-item"><a class="nav-link" href="../logout.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">

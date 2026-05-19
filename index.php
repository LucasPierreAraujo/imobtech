<?php
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/classes/JWT.php';
$usuario_logado = JWT::verificar($_COOKIE['token'] ?? '', getenv('JWT_SECRET'));
if (!$usuario_logado) { header('Location: login.php'); exit; }
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Imovel.php';
require_once __DIR__ . '/classes/ImovelDAO.php';

$db        = new Database();
$dao       = new ImovelDAO($db->conectar());
$resultado = $dao->listarComCliente();
?>
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
        .hero { background-color: #1a3c5e; color: white; padding: 60px 0; text-align: center; }
        .card { border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .badge-tipo { font-size: 0.75rem; }
        footer { background-color: #1a3c5e; color: #aaa; padding: 15px 0; margin-top: 50px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">Imobtech</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="imoveis/index.php">Imóveis</a></li>
                <li class="nav-item"><a class="nav-link" href="clientes/index.php">Clientes</a></li>
                <li class="nav-item"><a class="nav-link" href="contratos/index.php">Contratos</a></li>
                <li class="nav-item"><span class="nav-link text-warning">Olá, <?= htmlspecialchars($usuario_logado['nome'] ?? '') ?></span></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="hero">
    <h1>Bem-vindo à Imobtech</h1>
    <p class="lead">Encontre o imóvel ideal para você</p>
</div>

<div class="container mt-4">
    <h4 class="mb-3">Imóveis</h4>
    <div class="row">
        <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
        <?php
            $cores = ['disponivel' => 'success', 'vendido' => 'danger', 'alugado' => 'warning'];
            $cor = $cores[$row['status']] ?? 'secondary';
        ?>
        <div class="col-md-4">
            <div class="card">
                <?php if ($row['foto']): ?>
                <img src="<?= $row['foto'] ?>" class="card-img-top" style="height:180px;object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <span class="badge bg-secondary badge-tipo"><?= ucfirst($row['tipo']) ?></span>
                    <span class="badge bg-info badge-tipo"><?= ucfirst($row['finalidade']) ?></span>
                    <span class="badge bg-<?= $cor ?> badge-tipo">
                        <?= ucfirst($row['status']) ?>
                        <?php if ($row['cliente_nome'] && $row['status'] !== 'disponivel'): ?>
                            - <?= htmlspecialchars(explode(' ', $row['cliente_nome'])[0]) ?>
                        <?php endif; ?>
                    </span>
                    <h5 class="card-title mt-2"><?= htmlspecialchars($row['titulo']) ?></h5>
                    <p class="text-muted mb-1"><?= htmlspecialchars($row['bairro']) ?> - <?= htmlspecialchars($row['cidade']) ?></p>
                    <?php if ($row['quartos'] > 0): ?>
                    <small><?= $row['quartos'] ?> quartos | <?= $row['banheiros'] ?> banheiros | <?= $row['vagas'] ?> vagas</small><br>
                    <?php endif; ?>
                    <?php if ($row['area']): ?>
                    <small><?= $row['area'] ?> m²</small><br>
                    <?php endif; ?>
                    <strong class="text-success d-block mt-2">R$ <?= number_format($row['valor'], 2, ',', '.') ?></strong>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<footer class="text-center fixed-bottom">
    <p class="mb-0">Imobtech &copy; <?= date('Y') ?></p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

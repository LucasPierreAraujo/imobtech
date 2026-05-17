<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';

$db = new Database();
$conn = $db->conectar();
$cliente = new Cliente($conn);

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente->nome = $_POST['nome'];
    $cliente->cpf = $_POST['cpf'];
    $cliente->email = $_POST['email'];
    $cliente->telefone = $_POST['telefone'];

    if ($cliente->criar()) {
        header('Location: index.php?msg=Cliente cadastrado com sucesso!');
        exit;
    } else {
        $erro = 'Erro ao cadastrar cliente.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h3 class="mb-3">Novo Cliente</h3>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nome completo</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>CPF</label>
                    <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000">
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

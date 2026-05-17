<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';

$db = new Database();
$conn = $db->conectar();
$cliente = new Cliente($conn);

$cliente->id = $_GET['id'] ?? 0;
$dados = $cliente->buscarPorId();

if (!$dados) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente->id = $_POST['id'];
    $cliente->nome = $_POST['nome'];
    $cliente->cpf = $_POST['cpf'];
    $cliente->email = $_POST['email'];
    $cliente->telefone = $_POST['telefone'];

    if ($cliente->atualizar()) {
        header('Location: index.php?msg=Cliente atualizado com sucesso!');
        exit;
    } else {
        $erro = 'Erro ao atualizar cliente.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h3 class="mb-3">Editar Cliente</h3>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $dados['id'] ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nome completo</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados['nome']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>CPF</label>
                    <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($dados['cpf']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($dados['email']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($dados['telefone']) ?>">
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

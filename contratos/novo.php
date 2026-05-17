<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Contrato.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/Cliente.php';

$db = new Database();
$conn = $db->conectar();

$contrato = new Contrato($conn);
$imovel = new Imovel($conn);
$cliente = new Cliente($conn);

$imoveis = $imovel->listar();
$clientes = $cliente->listar();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contrato->imovel_id = $_POST['imovel_id'];
    $contrato->cliente_id = $_POST['cliente_id'];
    $contrato->tipo = $_POST['tipo'];
    $contrato->valor_total = $_POST['valor_total'];
    $contrato->parcelas = $_POST['parcelas'];
    $contrato->data_inicio = $_POST['data_inicio'];
    $contrato->data_fim = $_POST['data_fim'];

    if ($contrato->criar()) {
        $imovel->id = $_POST['imovel_id'];
        $imovel->status = $_POST['tipo'] === 'aluguel' ? 'alugado' : 'vendido';
        $imovel->atualizarStatus();
        header('Location: index.php?msg=Contrato cadastrado com sucesso!');
        exit;
    } else {
        $erro = 'Erro ao cadastrar contrato.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h3 class="mb-3">Novo Contrato</h3>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Imóvel</label>
                    <select name="imovel_id" class="form-select" required>
                        <option value="">Selecione um imóvel</option>
                        <?php while ($i = $imoveis->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['titulo']) ?> (<?= ucfirst($i['tipo']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Cliente</label>
                    <select name="cliente_id" class="form-select" required>
                        <option value="">Selecione um cliente</option>
                        <?php while ($c = $clientes->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Tipo de Contrato</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Selecione</option>
                        <option value="aluguel">Aluguel</option>
                        <option value="compra">Compra</option>
                        <option value="financiamento">Financiamento</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Valor Total (R$)</label>
                    <input type="number" step="0.01" name="valor_total" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Número de Parcelas</label>
                    <input type="number" name="parcelas" class="form-control" value="1" min="1">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Data de Início</label>
                    <input type="date" name="data_inicio" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Data de Fim</label>
                    <input type="date" name="data_fim" class="form-control">
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

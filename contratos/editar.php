<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/ImovelDAO.php';
require_once __DIR__ . '/../classes/Cliente.php';
require_once __DIR__ . '/../classes/ClienteDAO.php';
require_once __DIR__ . '/../classes/Contrato.php';
require_once __DIR__ . '/../classes/ContratoDAO.php';

$db          = new Database();
$conn        = $db->conectar();
$imovelDAO   = new ImovelDAO($conn);
$clienteDAO  = new ClienteDAO($conn);
$contratoDAO = new ContratoDAO($conn);

$dados = $contratoDAO->buscarPorId((int)($_GET['id'] ?? 0));
if (!$dados) { header('Location: index.php'); exit; }

$imoveis  = $imovelDAO->listar();
$clientes = $clienteDAO->listar();
$erro     = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contrato                  = new Contrato();
    $contrato->id              = (int)$_POST['id'];
    $contrato->imovel_id       = $_POST['imovel_id'];
    $contrato->cliente_id      = $_POST['cliente_id'];
    $contrato->tipo            = $_POST['tipo'];
    $contrato->valor_total     = $_POST['valor_total'];
    $contrato->parcelas        = $_POST['parcelas'];
    $contrato->data_inicio     = $_POST['data_inicio'];
    $contrato->data_fim        = $_POST['data_fim'];
    $contrato->forma_pagamento = $dados['forma_pagamento'] ?? null;
    $contrato->valor_entrada   = $dados['valor_entrada']   ?? 0;
    $contrato->valor_parcela   = $dados['valor_parcela']   ?? 0;
    $contrato->calcao          = $dados['calcao']          ?? 0;

    if ($contratoDAO->atualizar($contrato)) {
        header('Location: index.php?msg=Contrato atualizado com sucesso!');
        exit;
    } else {
        $erro = 'Erro ao atualizar contrato.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h3 class="mb-3">Editar Contrato</h3>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $dados['id'] ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Imóvel</label>
                    <select name="imovel_id" class="form-select" required>
                        <?php while ($i = $imoveis->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $i['id'] ?>" <?= $dados['imovel_id'] == $i['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['titulo']) ?> (<?= ucfirst($i['tipo']) ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Cliente</label>
                    <select name="cliente_id" class="form-select" required>
                        <?php while ($c = $clientes->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $c['id'] ?>" <?= $dados['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Tipo de Contrato</label>
                    <select name="tipo" class="form-select" required>
                        <?php foreach (['aluguel', 'compra', 'financiamento'] as $t): ?>
                        <option value="<?= $t ?>" <?= $dados['tipo'] == $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Valor Total (R$)</label>
                    <input type="number" step="0.01" name="valor_total" class="form-control" value="<?= $dados['valor_total'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Número de Parcelas</label>
                    <input type="number" name="parcelas" class="form-control" value="<?= $dados['parcelas'] ?>" min="1">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Data de Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= $dados['data_inicio'] ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Data de Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= $dados['data_fim'] ?>">
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

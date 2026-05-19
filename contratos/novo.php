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

$imoveis  = $imovelDAO->listar();
$clientes = $clienteDAO->listar();
$erro     = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo            = $_POST['tipo'] ?? '';
    $forma_pagamento = null;
    $valor_entrada   = 0;
    $valor_parcela   = 0;
    $parcelas        = 1;
    $valor_total     = 0;

    switch ($tipo) {
        case 'aluguel':
            $valor_parcela = abs((float)($_POST['aluguel_mensal'] ?? 0));
            $parcelas      = abs((int)($_POST['aluguel_meses'] ?? 0));
            $valor_total   = $valor_parcela * $parcelas;
            break;

        case 'compra':
            $forma_pagamento = $_POST['forma_pagamento'] ?? '';
            if ($forma_pagamento === 'cartao_credito') {
                $valor_parcela = abs((float)($_POST['compra_valor_parcela'] ?? 0));
                $parcelas      = abs((int)($_POST['compra_parcelas'] ?? 0));
                $valor_total   = $valor_parcela * $parcelas;
            } else {
                $valor_total   = abs((float)($_POST['compra_valor'] ?? 0));
                $parcelas      = 1;
                $valor_parcela = $valor_total;
            }
            break;

        case 'financiamento':
            $valor_entrada = abs((float)($_POST['fin_entrada'] ?? 0));
            $valor_parcela = abs((float)($_POST['fin_valor_parcela'] ?? 0));
            $parcelas      = abs((int)($_POST['fin_parcelas'] ?? 0));
            $valor_total   = $valor_entrada + ($valor_parcela * $parcelas);
            break;
    }

    $contrato                  = new Contrato();
    $contrato->imovel_id       = $_POST['imovel_id'];
    $contrato->cliente_id      = $_POST['cliente_id'];
    $contrato->tipo            = $tipo;
    $contrato->forma_pagamento = $forma_pagamento;
    $contrato->valor_entrada   = $valor_entrada;
    $contrato->valor_parcela   = $valor_parcela;
    $contrato->parcelas        = $parcelas;
    $contrato->valor_total     = $valor_total;
    $contrato->data_inicio     = $_POST['data_inicio'];
    $contrato->data_fim        = $_POST['data_fim'];

    if ($contratoDAO->criar($contrato)) {
        $novoStatus = $tipo === 'aluguel' ? 'alugado' : 'vendido';
        $imovelDAO->atualizarStatus($_POST['imovel_id'], $novoStatus);
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
                    <select name="tipo" id="tipo" class="form-select" required onchange="atualizarTipo()">
                        <option value="">Selecione</option>
                        <option value="aluguel">Aluguel</option>
                        <option value="compra">Compra</option>
                        <option value="financiamento">Financiamento</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Data de Início</label>
                    <input type="date" name="data_inicio" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Data de Fim</label>
                    <input type="date" name="data_fim" class="form-control">
                </div>
            </div>

            <div id="section-aluguel" style="display:none">
                <hr>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Valor mensal (R$)</label>
                        <input type="number" step="0.01" id="aluguel_mensal" name="aluguel_mensal" class="form-control" min="0" oninput="calcular()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Número de meses</label>
                        <input type="number" id="aluguel_meses" name="aluguel_meses" class="form-control" min="1" oninput="calcular()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Total a pagar</label>
                        <div class="form-control bg-light fw-bold text-success" id="total-aluguel">R$ 0,00</div>
                    </div>
                </div>
            </div>

            <div id="section-compra" style="display:none">
                <hr>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Forma de Pagamento</label>
                        <select name="forma_pagamento" id="forma_pagamento" class="form-select" onchange="atualizarFormaPagamento()">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="debito">Débito</option>
                            <option value="pix">Pix</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                        </select>
                    </div>
                </div>

                <div id="section-avista">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Valor (R$)</label>
                            <input type="number" step="0.01" id="compra_valor" name="compra_valor" class="form-control" min="0" oninput="calcular()">
                        </div>
                    </div>
                </div>

                <div id="section-parcelado" style="display:none">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Número de parcelas</label>
                            <input type="number" id="compra_parcelas" name="compra_parcelas" class="form-control" min="1" oninput="calcular()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Valor da parcela (R$)</label>
                            <input type="number" step="0.01" id="compra_valor_parcela" name="compra_valor_parcela" class="form-control" min="0" oninput="calcular()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Total a pagar</label>
                            <div class="form-control bg-light fw-bold text-success" id="total-parcelado">R$ 0,00</div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="section-financiamento" style="display:none">
                <hr>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Valor de entrada (R$)</label>
                        <input type="number" step="0.01" id="fin_entrada" name="fin_entrada" class="form-control" min="0" oninput="calcular()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Número de parcelas</label>
                        <input type="number" id="fin_parcelas" name="fin_parcelas" class="form-control" min="1" oninput="calcular()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Valor da parcela (R$)</label>
                        <input type="number" step="0.01" id="fin_valor_parcela" name="fin_valor_parcela" class="form-control" min="0" oninput="calcular()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Total a pagar</label>
                        <div class="form-control bg-light fw-bold text-success" id="total-financiamento">R$ 0,00</div>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary mt-2">Cancelar</a>
            <button type="submit" class="btn btn-primary mt-2">Salvar</button>
        </form>
    </div>
</div>

<script>
function fmt(v) {
    return 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function atualizarTipo() {
    const tipo = document.getElementById('tipo').value;
    document.getElementById('section-aluguel').style.display       = tipo === 'aluguel'       ? '' : 'none';
    document.getElementById('section-compra').style.display        = tipo === 'compra'        ? '' : 'none';
    document.getElementById('section-financiamento').style.display = tipo === 'financiamento' ? '' : 'none';
    calcular();
}

function atualizarFormaPagamento() {
    const parcelado = document.getElementById('forma_pagamento').value === 'cartao_credito';
    document.getElementById('section-avista').style.display    = parcelado ? 'none' : '';
    document.getElementById('section-parcelado').style.display = parcelado ? '' : 'none';
    calcular();
}

function calcular() {
    const tipo = document.getElementById('tipo').value;

    if (tipo === 'aluguel') {
        const mensal = parseFloat(document.getElementById('aluguel_mensal').value) || 0;
        const meses  = parseInt(document.getElementById('aluguel_meses').value)    || 0;
        document.getElementById('total-aluguel').textContent = fmt(mensal * meses);

    } else if (tipo === 'compra') {
        if (document.getElementById('forma_pagamento').value === 'cartao_credito') {
            const parc = parseInt(document.getElementById('compra_parcelas').value)       || 0;
            const val  = parseFloat(document.getElementById('compra_valor_parcela').value) || 0;
            document.getElementById('total-parcelado').textContent = fmt(parc * val);
        }

    } else if (tipo === 'financiamento') {
        const entrada = parseFloat(document.getElementById('fin_entrada').value)       || 0;
        const parc    = parseInt(document.getElementById('fin_parcelas').value)        || 0;
        const val     = parseFloat(document.getElementById('fin_valor_parcela').value) || 0;
        document.getElementById('total-financiamento').textContent = fmt(entrada + (parc * val));
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

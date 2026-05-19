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

$todosImoveis = $imovelDAO->listar()->fetchAll(PDO::FETCH_ASSOC);
$clientes     = $clienteDAO->listar()->fetchAll(PDO::FETCH_ASSOC);
$erro         = '';

$calcMeses = function($inicio, $fim) {
    if (!$inicio || !$fim) return 0;
    $d1 = new DateTime($inicio);
    $d2 = new DateTime($fim);
    $m  = ($d2->format('Y') - $d1->format('Y')) * 12 + ($d2->format('m') - $d1->format('m'));
    return max(0, $m);
};

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo            = $_POST['tipo'] ?? '';
    $dataInicio      = $_POST['data_inicio'] ?? '';
    $dataFim         = $_POST['data_fim'] ?? '';
    $forma_pagamento = null;
    $valor_entrada   = 0;
    $valor_parcela   = 0;
    $calcao          = 0;
    $parcelas        = 1;
    $valor_total     = 0;

    switch ($tipo) {
        case 'aluguel':
            $valor_parcela = abs((float)($_POST['aluguel_mensal'] ?? 0));
            $parcelas      = $calcMeses($dataInicio, $dataFim) ?: abs((int)($_POST['aluguel_meses'] ?? 0));
            $calcao        = $valor_parcela * 2;
            $valor_total   = $valor_parcela * $parcelas;
            break;

        case 'compra':
            $forma_pagamento = $_POST['forma_pagamento'] ?? '';
            $valor_total     = abs((float)($_POST['compra_valor'] ?? 0));
            $parcelas        = 1;
            $valor_parcela   = $valor_total;
            break;

        case 'financiamento':
            $valor_entrada = abs((float)($_POST['fin_entrada'] ?? 0));
            $valor_parcela = abs((float)($_POST['fin_valor_parcela'] ?? 0));
            $parcelas      = $calcMeses($dataInicio, $dataFim) ?: abs((int)($_POST['fin_parcelas'] ?? 0));
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
    $contrato->calcao          = $calcao;
    $contrato->parcelas        = $parcelas;
    $contrato->valor_total     = $valor_total;
    $contrato->data_inicio     = $dataInicio;
    $contrato->data_fim        = $dataFim;

    if ($contratoDAO->criar($contrato)) {
        $novoStatus = $tipo === 'aluguel' ? 'alugado' : 'vendido';
        $imovelDAO->atualizarStatus($_POST['imovel_id'], $novoStatus);
        header('Location: index.php?msg=Contrato cadastrado com sucesso!');
        exit;
    } else {
        $erro = 'Erro ao cadastrar contrato.';
    }
}

$imoveisJson = [];
$precos      = [];
foreach ($todosImoveis as $im) {
    $imoveisJson[] = [
        'id'        => (int)$im['id'],
        'titulo'    => $im['titulo'],
        'tipo'      => $im['tipo'],
        'finalidade'=> $im['finalidade'],
        'valor'     => (float)$im['valor'],
    ];
    $precos[(int)$im['id']] = (float)$im['valor'];
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
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" oninput="onDatasChange()">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Data de Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control" oninput="onDatasChange()">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Imóvel</label>
                    <select name="imovel_id" id="imovel_id" class="form-select" required onchange="preencherValor()" disabled>
                        <option value="">Selecione o tipo de contrato primeiro</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Cliente</label>
                    <select name="cliente_id" class="form-select" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Aluguel -->
            <div id="section-aluguel" style="display:none">
                <hr>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Valor mensal (R$)</label>
                        <input type="number" step="0.01" id="aluguel_mensal" name="aluguel_mensal" class="form-control" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Número de meses</label>
                        <input type="number" id="aluguel_meses" name="aluguel_meses" class="form-control" min="1" oninput="calcular()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Calção (2 meses adiantado)</label>
                        <div class="form-control bg-light fw-bold" id="display-calcao">R$ 0,00</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Valor total até o final do contrato</label>
                        <div class="form-control bg-light fw-bold text-success" id="total-aluguel">R$ 0,00</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Multa contratual</label>
                        <div class="form-control bg-light text-danger fw-bold" id="display-multa">R$ 0,00</div>
                        <small class="text-muted">Valor a pagar caso rescinda antes do fim do contrato</small>
                    </div>
                </div>
            </div>

            <!-- Compra -->
            <div id="section-compra" style="display:none">
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Forma de Pagamento</label>
                        <select name="forma_pagamento" id="forma_pagamento" class="form-select">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="debito">Débito</option>
                            <option value="pix">Pix</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Valor (R$)</label>
                        <input type="number" step="0.01" id="compra_valor" name="compra_valor" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <!-- Financiamento -->
            <div id="section-financiamento" style="display:none">
                <hr>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Valor de entrada (R$)</label>
                        <input type="number" step="0.01" id="fin_entrada" name="fin_entrada" class="form-control" min="0" oninput="calcular()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Número de parcelas</label>
                        <input type="number" id="fin_parcelas" name="fin_parcelas" class="form-control" readonly>
                        <small id="info-parcelas" class="text-muted"></small>
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
const imoveis = <?= json_encode($imoveisJson) ?>;
const precos  = <?= json_encode($precos) ?>;

function fmt(v) {
    return 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function mesesEntreDatas() {
    const inicio = document.getElementById('data_inicio').value;
    const fim    = document.getElementById('data_fim').value;
    if (!inicio || !fim) return 0;
    const d1 = new Date(inicio);
    const d2 = new Date(fim);
    const m  = (d2.getFullYear() - d1.getFullYear()) * 12 + (d2.getMonth() - d1.getMonth());
    return m > 0 ? m : 0;
}

function filtrarImoveis() {
    const tipo   = document.getElementById('tipo').value;
    const select = document.getElementById('imovel_id');

    select.innerHTML = '';
    select.disabled  = !tipo;

    if (!tipo) {
        select.innerHTML = '<option value="">Selecione o tipo de contrato primeiro</option>';
        return;
    }

    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = 'Selecione um imóvel';
    select.appendChild(opt0);

    imoveis
        .filter(i => tipo === 'aluguel'
            ? i.finalidade === 'alugar'
            : i.finalidade === 'comprar' || i.finalidade === 'financiamento')
        .forEach(i => {
            const opt = document.createElement('option');
            opt.value = i.id;
            opt.textContent = i.titulo + ' (' + i.tipo.charAt(0).toUpperCase() + i.tipo.slice(1) + ')';
            select.appendChild(opt);
        });

    preencherValor();
}

function preencherValor() {
    const id    = document.getElementById('imovel_id').value;
    const preco = precos[id] || 0;
    document.getElementById('aluguel_mensal').value = preco ? preco.toFixed(2) : '';
    document.getElementById('compra_valor').value   = preco ? preco.toFixed(2) : '';
    calcular();
}

function atualizarTipo() {
    const tipo = document.getElementById('tipo').value;
    document.getElementById('section-aluguel').style.display       = tipo === 'aluguel'       ? '' : 'none';
    document.getElementById('section-compra').style.display        = tipo === 'compra'        ? '' : 'none';
    document.getElementById('section-financiamento').style.display = tipo === 'financiamento' ? '' : 'none';
    filtrarImoveis();
}

function onDatasChange() {
    const meses = mesesEntreDatas();
    const tipo  = document.getElementById('tipo').value;

    if (tipo === 'aluguel' && meses > 0) {
        document.getElementById('aluguel_meses').value = meses;
    }
    if (tipo === 'financiamento') {
        document.getElementById('fin_parcelas').value = meses || '';
        document.getElementById('info-parcelas').textContent = meses > 0
            ? meses + ' parcelas calculadas pelas datas.'
            : '';
    }
    calcular();
}

function calcular() {
    const tipo = document.getElementById('tipo').value;

    if (tipo === 'aluguel') {
        const mensal = parseFloat(document.getElementById('aluguel_mensal').value) || 0;
        const meses  = parseInt(document.getElementById('aluguel_meses').value) || 0;
        const total  = mensal * meses;
        document.getElementById('total-aluguel').textContent  = fmt(total);
        document.getElementById('display-calcao').textContent = fmt(mensal * 2);
        document.getElementById('display-multa').textContent  = fmt(total);

    } else if (tipo === 'financiamento') {
        const entrada = parseFloat(document.getElementById('fin_entrada').value) || 0;
        const parc    = parseInt(document.getElementById('fin_parcelas').value) || 0;
        const val     = parseFloat(document.getElementById('fin_valor_parcela').value) || 0;
        document.getElementById('total-financiamento').textContent = fmt(entrada + (parc * val));
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Contrato.php';

$db = new Database();
$conn = $db->conectar();
$contrato = new Contrato($conn);
$resultado = $contrato->listar();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Contratos</h3>
    <a href="novo.php" class="btn btn-primary">+ Novo Contrato</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Imóvel</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Valor Total</th>
                    <th>Parcelas</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['imovel_titulo']) ?></td>
                    <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
                    <td><?= ucfirst($row['tipo']) ?></td>
                    <td>R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                    <td><?= $row['parcelas'] ?>x</td>
                    <td><?= $row['data_inicio'] ? date('d/m/Y', strtotime($row['data_inicio'])) : '-' ?></td>
                    <td><?= $row['data_fim'] ? date('d/m/Y', strtotime($row['data_fim'])) : '-' ?></td>
                    <td>
                        <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="deletar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este contrato?')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

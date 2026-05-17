<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';

$db = new Database();
$conn = $db->conectar();
$imovel = new Imovel($conn);
$resultado = $imovel->listarComCliente();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Imóveis</h3>
    <a href="novo.php" class="btn btn-primary">+ Novo Imóvel</a>
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
                    <th>Foto</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Finalidade</th>
                    <th>Cidade</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <?php if ($row['foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" style="height:45px;width:65px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td><?= ucfirst($row['tipo']) ?></td>
                    <td><?= ucfirst($row['finalidade']) ?></td>
                    <td><?= htmlspecialchars($row['cidade']) ?></td>
                    <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                    <td>
                        <?php
                        $cores = ['disponivel' => 'success', 'vendido' => 'danger', 'alugado' => 'warning'];
                        $cor = $cores[$row['status']] ?? 'secondary';
                        $primeiroNome = $row['cliente_nome'] ? htmlspecialchars(explode(' ', $row['cliente_nome'])[0]) : '';
                        ?>
                        <span class="badge bg-<?= $cor ?>">
                            <?= ucfirst($row['status']) ?>
                            <?php if ($primeiroNome && $row['status'] !== 'disponivel'): ?>
                                - <?= $primeiroNome ?>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td>
                        <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="deletar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este imóvel?')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

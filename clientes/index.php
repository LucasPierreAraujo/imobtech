<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';
require_once __DIR__ . '/../classes/ClienteDAO.php';

$db  = new Database();
$dao = new ClienteDAO($db->conectar());
$resultado = $dao->listar();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Clientes</h3>
    <a href="novo.php" class="btn btn-primary">+ Novo Cliente</a>
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
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><?= htmlspecialchars($row['cpf']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="deletar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este cliente?')">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

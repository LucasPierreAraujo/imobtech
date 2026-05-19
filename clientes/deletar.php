<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';
require_once __DIR__ . '/../classes/ClienteDAO.php';

$db  = new Database();
$dao = new ClienteDAO($db->conectar());
$id  = (int)($_GET['id'] ?? 0);

if ($dao->deletar($id)) {
    header('Location: index.php?msg=' . urlencode('Cliente excluído com sucesso!'));
} else {
    header('Location: index.php?msg=' . urlencode('Erro ao excluir. Verifique se há contratos vinculados.'));
}
exit;

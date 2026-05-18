<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';

$db = new Database();
$conn = $db->conectar();
$cliente = new Cliente($conn);

$cliente->id = (int)($_GET['id'] ?? 0);

if ($cliente->deletar()) {
    header('Location: index.php?msg=Cliente excluído com sucesso!');
} else {
    header('Location: index.php?msg=Erro ao excluir. Verifique se há contratos vinculados.');
}
exit;

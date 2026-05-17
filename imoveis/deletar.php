<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';

$db = new Database();
$conn = $db->conectar();
$imovel = new Imovel($conn);

$imovel->id = $_GET['id'] ?? 0;

if ($imovel->deletar()) {
    header('Location: index.php?msg=Imóvel excluído com sucesso!');
} else {
    header('Location: index.php?msg=Erro ao excluir. Verifique se há contratos vinculados.');
}
exit;

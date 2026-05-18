<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/ImovelDAO.php';

$db  = new Database();
$dao = new ImovelDAO($db->conectar());
$id  = (int)($_GET['id'] ?? 0);

if ($dao->deletar($id)) {
    header('Location: index.php?msg=Imóvel excluído com sucesso!');
} else {
    header('Location: index.php?msg=Erro ao excluir. Verifique se há contratos vinculados.');
}
exit;

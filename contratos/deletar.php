<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/ImovelDAO.php';
require_once __DIR__ . '/../classes/Contrato.php';
require_once __DIR__ . '/../classes/ContratoDAO.php';

$db          = new Database();
$conn        = $db->conectar();
$contratoDAO = new ContratoDAO($conn);
$imovelDAO   = new ImovelDAO($conn);

$id    = (int)($_GET['id'] ?? 0);
$dados = $contratoDAO->buscarPorId($id);

if ($contratoDAO->deletar($id)) {
    if ($dados) {
        $imovelDAO->atualizarStatus($dados['imovel_id'], 'disponivel');
    }
    header('Location: index.php?msg=' . urlencode('Contrato excluído com sucesso!'));
} else {
    header('Location: index.php?msg=Erro ao excluir contrato.');
}
exit;

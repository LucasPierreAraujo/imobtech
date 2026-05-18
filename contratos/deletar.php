<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Contrato.php';
require_once __DIR__ . '/../classes/Imovel.php';

$db = new Database();
$conn = $db->conectar();
$contrato = new Contrato($conn);
$imovel = new Imovel($conn);

$contrato->id = (int)($_GET['id'] ?? 0);
$dados = $contrato->buscarPorId();

if ($contrato->deletar()) {
    if ($dados) {
        $imovel->id = $dados['imovel_id'];
        $imovel->status = 'disponivel';
        $imovel->atualizarStatus();
    }
    header('Location: index.php?msg=Contrato excluído com sucesso!');
} else {
    header('Location: index.php?msg=Erro ao excluir contrato.');
}
exit;

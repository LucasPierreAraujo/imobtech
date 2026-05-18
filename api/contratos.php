<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Contrato.php';
require_once __DIR__ . '/../classes/ContratoDAO.php';

autenticarApi();

$db  = new Database();
$dao = new ContratoDAO($db->conectar());

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lista = $dao->listar()->fetchAll(PDO::FETCH_ASSOC);
    respostaJson($lista);
}

respostaJson(['erro' => 'Método não permitido.'], 405);

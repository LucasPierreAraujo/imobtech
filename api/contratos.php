<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Contrato.php';

autenticarApi();

$db = new Database();
$conn = $db->conectar();
$contrato = new Contrato($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $contrato->listar();
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respostaJson($lista);
}

respostaJson(['erro' => 'Método não permitido.'], 405);

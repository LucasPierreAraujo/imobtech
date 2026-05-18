<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cliente.php';

autenticarApi();

$db = new Database();
$conn = $db->conectar();
$cliente = new Cliente($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $cliente->listar();
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respostaJson($lista);
}

respostaJson(['erro' => 'Método não permitido.'], 405);

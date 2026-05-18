<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';

autenticarApi();

$db = new Database();
$conn = $db->conectar();
$imovel = new Imovel($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $imovel->listarComCliente();
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respostaJson($lista);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        respostaJson(['erro' => 'Dados inválidos.'], 400);
    }

    $imovel->tipo       = $body['tipo'] ?? '';
    $imovel->finalidade = $body['finalidade'] ?? '';
    $imovel->titulo     = $body['titulo'] ?? '';
    $imovel->descricao  = $body['descricao'] ?? '';
    $imovel->valor      = $body['valor'] ?? 0;
    $imovel->area       = $body['area'] ?? 0;
    $imovel->quartos    = $body['quartos'] ?? 0;
    $imovel->banheiros  = $body['banheiros'] ?? 0;
    $imovel->vagas      = $body['vagas'] ?? 0;
    $imovel->cidade     = $body['cidade'] ?? '';
    $imovel->bairro     = $body['bairro'] ?? '';
    $imovel->status     = 'disponivel';
    $imovel->foto       = null;

    if ($imovel->criar()) {
        respostaJson(['mensagem' => 'Imóvel cadastrado com sucesso.'], 201);
    } else {
        respostaJson(['erro' => 'Dados inválidos ou tipo/finalidade incorretos.'], 400);
    }
}

respostaJson(['erro' => 'Método não permitido.'], 405);

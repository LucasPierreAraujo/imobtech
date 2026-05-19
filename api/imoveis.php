<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/ImovelDAO.php';

autenticarApi();

$db  = new Database();
$dao = new ImovelDAO($db->conectar());

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt  = $dao->listarComCliente();
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respostaJson($lista);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if ($body === null) respostaJson(['erro' => 'JSON inválido.'], 400);

    $tiposValidos       = ['casa', 'apartamento', 'chacara', 'terreno', 'sitio', 'empresarial'];
    $finalidadesValidas = ['alugar', 'comprar', 'financiamento'];

    $tipo       = $body['tipo'] ?? '';
    $finalidade = $body['finalidade'] ?? '';
    $titulo     = trim($body['titulo'] ?? '');
    $cidade     = trim($body['cidade'] ?? '');

    if (!in_array($tipo, $tiposValidos) || !in_array($finalidade, $finalidadesValidas)) {
        respostaJson(['erro' => 'Tipo ou finalidade inválidos.'], 422);
    }
    if ($titulo === '' || $cidade === '') {
        respostaJson(['erro' => 'Os campos titulo e cidade são obrigatórios.'], 422);
    }

    $imovel             = new Imovel();
    $imovel->tipo       = $tipo;
    $imovel->finalidade = $finalidade;
    $imovel->titulo     = $titulo;
    $imovel->descricao  = $body['descricao'] ?? '';
    $imovel->valor      = $body['valor'] ?? 0;
    $imovel->area       = $body['area'] ?? 0;
    $imovel->quartos    = $body['quartos'] ?? 0;
    $imovel->banheiros  = $body['banheiros'] ?? 0;
    $imovel->vagas      = $body['vagas'] ?? 0;
    $imovel->cidade     = $cidade;
    $imovel->bairro     = $body['bairro'] ?? '';
    $imovel->foto       = null;

    try {
        if ($dao->criar($imovel)) {
            respostaJson(['mensagem' => 'Imóvel cadastrado com sucesso.'], 201);
        }
        respostaJson(['erro' => 'Erro ao cadastrar imóvel.'], 500);
    } catch (Exception $e) {
        respostaJson(['erro' => 'Erro interno do servidor.'], 500);
    }
}

respostaJson(['erro' => 'Método não permitido.'], 405);

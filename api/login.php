<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Usuario.php';
require_once __DIR__ . '/../classes/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respostaJson(['erro' => 'Método não permitido.'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if ($body === null) respostaJson(['erro' => 'JSON inválido.'], 400);

$login = $body['usuario'] ?? '';
$senha = $body['senha'] ?? '';

if ($login === '' || $senha === '') {
    respostaJson(['erro' => 'Os campos usuario e senha são obrigatórios.'], 422);
}

$usuario          = new Usuario();
$usuario->usuario = $login;
$usuario->senha   = $senha;

$db    = new Database();
$dao   = new UsuarioDAO($db->conectar());
$dados = $dao->login($usuario);

if (!$dados) {
    respostaJson(['erro' => 'Usuário ou senha inválidos.'], 401);
}

$payload = [
    'id'   => $dados['id'],
    'nome' => $dados['nome'],
    'exp'  => time() + (8 * 60 * 60)
];
$token = JWT::gerar($payload, getenv('JWT_SECRET'));

respostaJson(['token' => $token, 'nome' => $dados['nome']]);

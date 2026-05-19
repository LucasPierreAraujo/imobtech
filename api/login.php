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

class LoginController {
    private UsuarioDAO $dao;

    public function __construct() {
        $this->dao = new UsuarioDAO((new Database())->conectar());
    }

    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Api::responder(['erro' => 'Método não permitido.'], 405);
        }
        $this->post();
    }

    private function post(): void {
        $body = json_decode(file_get_contents('php://input'), true);
        if ($body === null) Api::responder(['erro' => 'JSON inválido.'], 400);

        $login = $body['usuario'] ?? '';
        $senha = $body['senha'] ?? '';

        if ($login === '' || $senha === '') {
            Api::responder(['erro' => 'Os campos usuario e senha são obrigatórios.'], 422);
        }

        $usuario          = new Usuario();
        $usuario->usuario = $login;
        $usuario->senha   = $senha;

        $dados = $this->dao->login($usuario);

        if (!$dados) {
            Api::responder(['erro' => 'Usuário ou senha inválidos.'], 401);
        }

        $payload = [
            'id'   => $dados['id'],
            'nome' => $dados['nome'],
            'exp'  => time() + (8 * 60 * 60)
        ];

        Api::responder(['token' => JWT::gerar($payload, getenv('JWT_SECRET')), 'nome' => $dados['nome']]);
    }
}

(new LoginController())->handle();

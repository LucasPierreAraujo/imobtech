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

class ImoveisController {
    private ImovelDAO $dao;

    private const TIPOS       = ['casa', 'apartamento', 'chacara', 'terreno', 'sitio', 'empresarial'];
    private const FINALIDADES = ['alugar', 'comprar', 'financiamento'];

    public function __construct() {
        Api::autenticar();
        $this->dao = new ImovelDAO((new Database())->conectar());
    }

    public function handle(): void {
        match($_SERVER['REQUEST_METHOD']) {
            'GET'   => $this->get(),
            'POST'  => $this->post(),
            default => Api::responder(['erro' => 'Método não permitido.'], 405)
        };
    }

    private function get(): void {
        Api::responder($this->dao->listarComCliente()->fetchAll(PDO::FETCH_ASSOC));
    }

    private function post(): void {
        $body = json_decode(file_get_contents('php://input'), true);
        if ($body === null) Api::responder(['erro' => 'JSON inválido.'], 400);

        $tipo       = $body['tipo'] ?? '';
        $finalidade = $body['finalidade'] ?? '';
        $titulo     = trim($body['titulo'] ?? '');
        $cidade     = trim($body['cidade'] ?? '');

        if (!in_array($tipo, self::TIPOS) || !in_array($finalidade, self::FINALIDADES)) {
            Api::responder(['erro' => 'Tipo ou finalidade inválidos.'], 422);
        }
        if ($titulo === '' || $cidade === '') {
            Api::responder(['erro' => 'Os campos titulo e cidade são obrigatórios.'], 422);
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
            if ($this->dao->criar($imovel)) {
                Api::responder(['mensagem' => 'Imóvel cadastrado com sucesso.'], 201);
            }
            Api::responder(['erro' => 'Erro ao cadastrar imóvel.'], 500);
        } catch (Exception $e) {
            Api::responder(['erro' => 'Erro interno do servidor.'], 500);
        }
    }
}

(new ImoveisController())->handle();

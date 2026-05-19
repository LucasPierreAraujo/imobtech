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

class ContratosController {
    private ContratoDAO $dao;

    public function __construct() {
        Api::autenticar();
        $this->dao = new ContratoDAO((new Database())->conectar());
    }

    public function handle(): void {
        match($_SERVER['REQUEST_METHOD']) {
            'GET'   => $this->get(),
            default => Api::responder(['erro' => 'Método não permitido.'], 405)
        };
    }

    private function get(): void {
        Api::responder($this->dao->listar()->fetchAll(PDO::FETCH_ASSOC));
    }
}

(new ContratosController())->handle();

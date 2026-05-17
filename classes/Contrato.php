<?php
class Contrato {
    private $conn;
    private $tabela = 'contratos';

    public $id;
    public $imovel_id;
    public $cliente_id;
    public $tipo;
    public $valor_total;
    public $parcelas;
    public $data_inicio;
    public $data_fim;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT c.*, i.titulo as imovel_titulo, cl.nome as cliente_nome
                FROM {$this->tabela} c
                JOIN imoveis i ON c.imovel_id = i.id
                JOIN clientes cl ON c.cliente_id = cl.id
                ORDER BY c.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function buscarPorId() {
        $sql = "SELECT c.*, i.titulo as imovel_titulo, cl.nome as cliente_nome
                FROM {$this->tabela} c
                JOIN imoveis i ON c.imovel_id = i.id
                JOIN clientes cl ON c.cliente_id = cl.id
                WHERE c.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar() {
        $sql = "INSERT INTO {$this->tabela} (imovel_id, cliente_id, tipo, valor_total, parcelas, data_inicio, data_fim)
                VALUES (:imovel_id, :cliente_id, :tipo, :valor_total, :parcelas, :data_inicio, :data_fim)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':imovel_id', $this->imovel_id);
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':valor_total', $this->valor_total);
        $stmt->bindParam(':parcelas', $this->parcelas);
        $stmt->bindParam(':data_inicio', $this->data_inicio);
        $stmt->bindParam(':data_fim', $this->data_fim);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function atualizar() {
        $sql = "UPDATE {$this->tabela} SET imovel_id=:imovel_id, cliente_id=:cliente_id, tipo=:tipo,
                valor_total=:valor_total, parcelas=:parcelas, data_inicio=:data_inicio, data_fim=:data_fim
                WHERE id=:id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':imovel_id', $this->imovel_id);
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':valor_total', $this->valor_total);
        $stmt->bindParam(':parcelas', $this->parcelas);
        $stmt->bindParam(':data_inicio', $this->data_inicio);
        $stmt->bindParam(':data_fim', $this->data_fim);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deletar() {
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}

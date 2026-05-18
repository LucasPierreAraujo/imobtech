<?php
class ContratoDAO {
    private $conn;
    private $tabela = 'contratos';

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

    public function buscarPorId($id) {
        $id  = (int)$id;
        $sql = "SELECT c.*, i.titulo as imovel_titulo, cl.nome as cliente_nome
                FROM {$this->tabela} c
                JOIN imoveis i ON c.imovel_id = i.id
                JOIN clientes cl ON c.cliente_id = cl.id
                WHERE c.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar(Contrato $contrato) {
        $sql = "INSERT INTO {$this->tabela} (imovel_id, cliente_id, tipo, valor_total, parcelas, data_inicio, data_fim)
                VALUES (:imovel_id, :cliente_id, :tipo, :valor_total, :parcelas, :data_inicio, :data_fim)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':imovel_id',  $contrato->imovel_id);
        $stmt->bindParam(':cliente_id', $contrato->cliente_id);
        $stmt->bindParam(':tipo',       $contrato->tipo);
        $stmt->bindParam(':valor_total',$contrato->valor_total);
        $stmt->bindParam(':parcelas',   $contrato->parcelas);
        $stmt->bindParam(':data_inicio',$contrato->data_inicio);
        $stmt->bindParam(':data_fim',   $contrato->data_fim);
        return $stmt->execute();
    }

    public function atualizar(Contrato $contrato) {
        $id  = (int)$contrato->id;
        $sql = "UPDATE {$this->tabela} SET imovel_id=:imovel_id, cliente_id=:cliente_id, tipo=:tipo,
                valor_total=:valor_total, parcelas=:parcelas, data_inicio=:data_inicio, data_fim=:data_fim
                WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':imovel_id',  $contrato->imovel_id);
        $stmt->bindParam(':cliente_id', $contrato->cliente_id);
        $stmt->bindParam(':tipo',       $contrato->tipo);
        $stmt->bindParam(':valor_total',$contrato->valor_total);
        $stmt->bindParam(':parcelas',   $contrato->parcelas);
        $stmt->bindParam(':data_inicio',$contrato->data_inicio);
        $stmt->bindParam(':data_fim',   $contrato->data_fim);
        $stmt->bindParam(':id',         $id);
        return $stmt->execute();
    }

    public function deletar($id) {
        $id  = (int)$id;
        $sql = "DELETE FROM {$this->tabela} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

<?php
class ClienteDAO {
    private $conn;
    private $tabela = 'clientes';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM {$this->tabela} ORDER BY nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function buscarPorId($id) {
        $id  = (int)$id;
        $sql = "SELECT * FROM {$this->tabela} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar(Cliente $cliente) {
        $sql = "INSERT INTO {$this->tabela} (nome, cpf, email, telefone) VALUES (:nome, :cpf, :email, :telefone)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome',     $cliente->nome);
        $stmt->bindParam(':cpf',      $cliente->cpf);
        $stmt->bindParam(':email',    $cliente->email);
        $stmt->bindParam(':telefone', $cliente->telefone);
        return $stmt->execute();
    }

    public function atualizar(Cliente $cliente) {
        $id  = (int)$cliente->id;
        $sql = "UPDATE {$this->tabela} SET nome=:nome, cpf=:cpf, email=:email, telefone=:telefone WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome',     $cliente->nome);
        $stmt->bindParam(':cpf',      $cliente->cpf);
        $stmt->bindParam(':email',    $cliente->email);
        $stmt->bindParam(':telefone', $cliente->telefone);
        $stmt->bindParam(':id',       $id);
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

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
        $nome     = htmlspecialchars(strip_tags($cliente->nome));
        $cpf      = htmlspecialchars(strip_tags($cliente->cpf));
        $email    = htmlspecialchars(strip_tags($cliente->email));
        $telefone = htmlspecialchars(strip_tags($cliente->telefone));

        $sql = "INSERT INTO {$this->tabela} (nome, cpf, email, telefone) VALUES (:nome, :cpf, :email, :telefone)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome',     $nome);
        $stmt->bindParam(':cpf',      $cpf);
        $stmt->bindParam(':email',    $email);
        $stmt->bindParam(':telefone', $telefone);
        return $stmt->execute();
    }

    public function atualizar(Cliente $cliente) {
        $id       = (int)$cliente->id;
        $nome     = htmlspecialchars(strip_tags($cliente->nome));
        $cpf      = htmlspecialchars(strip_tags($cliente->cpf));
        $email    = htmlspecialchars(strip_tags($cliente->email));
        $telefone = htmlspecialchars(strip_tags($cliente->telefone));

        $sql = "UPDATE {$this->tabela} SET nome=:nome, cpf=:cpf, email=:email, telefone=:telefone WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome',     $nome);
        $stmt->bindParam(':cpf',      $cpf);
        $stmt->bindParam(':email',    $email);
        $stmt->bindParam(':telefone', $telefone);
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

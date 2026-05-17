<?php
class Usuario {
    private $conn;
    private $tabela = 'usuarios';

    public $id;
    public $nome;
    public $usuario;
    public $senha;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $sql = "SELECT * FROM {$this->tabela} WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->senha, $row['senha'])) {
            return $row;
        }
        return false;
    }

    public function cadastrar() {
        $sql = "INSERT INTO {$this->tabela} (nome, usuario, senha) VALUES (:nome, :usuario, :senha)";
        $stmt = $this->conn->prepare($sql);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $hash = password_hash($this->senha, PASSWORD_DEFAULT);

        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->bindParam(':senha', $hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function usuarioExiste() {
        $sql = "SELECT id FROM {$this->tabela} WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}

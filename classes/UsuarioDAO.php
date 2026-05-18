<?php
class UsuarioDAO {
    private $conn;
    private $tabela = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login(Usuario $usuario) {
        $sql = "SELECT * FROM {$this->tabela} WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario->usuario);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($usuario->senha, $row['senha'])) {
            return $row;
        }
        return false;
    }

    public function criar(Usuario $usuario) {
        $nome    = htmlspecialchars(strip_tags($usuario->nome));
        $login   = htmlspecialchars(strip_tags($usuario->usuario));
        $hash    = password_hash($usuario->senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO {$this->tabela} (nome, usuario, senha) VALUES (:nome, :usuario, :senha)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome',    $nome);
        $stmt->bindParam(':usuario', $login);
        $stmt->bindParam(':senha',   $hash);
        return $stmt->execute();
    }

    public function usuarioExiste(Usuario $usuario) {
        $sql = "SELECT id FROM {$this->tabela} WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario->usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}

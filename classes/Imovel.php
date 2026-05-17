<?php
class Imovel {
    private $conn;
    private $tabela = 'imoveis';

    public $id;
    public $tipo;
    public $finalidade;
    public $titulo;
    public $descricao;
    public $valor;
    public $area;
    public $quartos;
    public $banheiros;
    public $vagas;
    public $cidade;
    public $bairro;
    public $status;
    public $foto;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM {$this->tabela} ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function listarComCliente() {
        $sql = "SELECT DISTINCT ON (i.id) i.*, cl.nome as cliente_nome
                FROM {$this->tabela} i
                LEFT JOIN contratos c ON c.imovel_id = i.id
                LEFT JOIN clientes cl ON cl.id = c.cliente_id
                ORDER BY i.id DESC, c.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function buscarPorId() {
        $sql = "SELECT * FROM {$this->tabela} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar() {
        $sql = "INSERT INTO {$this->tabela} (tipo, finalidade, titulo, descricao, valor, area, quartos, banheiros, vagas, cidade, bairro, status, foto)
                VALUES (:tipo, :finalidade, :titulo, :descricao, :valor, :area, :quartos, :banheiros, :vagas, :cidade, :bairro, :status, :foto)";
        $stmt = $this->conn->prepare($sql);

        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->finalidade = htmlspecialchars(strip_tags($this->finalidade));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->cidade = htmlspecialchars(strip_tags($this->cidade));
        $this->bairro = htmlspecialchars(strip_tags($this->bairro));

        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':finalidade', $this->finalidade);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':valor', $this->valor);
        $stmt->bindParam(':area', $this->area);
        $stmt->bindParam(':quartos', $this->quartos);
        $stmt->bindParam(':banheiros', $this->banheiros);
        $stmt->bindParam(':vagas', $this->vagas);
        $stmt->bindParam(':cidade', $this->cidade);
        $stmt->bindParam(':bairro', $this->bairro);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':foto', $this->foto);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function atualizar() {
        $sql = "UPDATE {$this->tabela} SET tipo=:tipo, finalidade=:finalidade, titulo=:titulo, descricao=:descricao,
                valor=:valor, area=:area, quartos=:quartos, banheiros=:banheiros, vagas=:vagas,
                cidade=:cidade, bairro=:bairro, status=:status, foto=:foto WHERE id=:id";
        $stmt = $this->conn->prepare($sql);

        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->finalidade = htmlspecialchars(strip_tags($this->finalidade));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->cidade = htmlspecialchars(strip_tags($this->cidade));
        $this->bairro = htmlspecialchars(strip_tags($this->bairro));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':finalidade', $this->finalidade);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':valor', $this->valor);
        $stmt->bindParam(':area', $this->area);
        $stmt->bindParam(':quartos', $this->quartos);
        $stmt->bindParam(':banheiros', $this->banheiros);
        $stmt->bindParam(':vagas', $this->vagas);
        $stmt->bindParam(':cidade', $this->cidade);
        $stmt->bindParam(':bairro', $this->bairro);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':foto', $this->foto);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function atualizarStatus() {
        $sql = "UPDATE {$this->tabela} SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
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

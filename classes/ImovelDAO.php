<?php
class ImovelDAO {
    private $conn;
    private $tabela = 'imoveis';

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

    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->tabela} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function criar(Imovel $imovel) {
        $tiposValidos       = ['casa', 'apartamento', 'chacara', 'terreno', 'sitio', 'empresarial'];
        $finalidadesValidas = ['alugar', 'comprar', 'financiamento'];

        if (!in_array($imovel->tipo, $tiposValidos) || !in_array($imovel->finalidade, $finalidadesValidas)) {
            return false;
        }

        $valor     = abs((float)$imovel->valor);
        $area      = abs((float)$imovel->area);
        $quartos   = abs((int)$imovel->quartos);
        $banheiros = abs((int)$imovel->banheiros);
        $vagas     = abs((int)$imovel->vagas);
        $status    = 'disponivel';

        $sql = "INSERT INTO {$this->tabela} (tipo, finalidade, titulo, descricao, valor, area, quartos, banheiros, vagas, cidade, bairro, status, foto)
                VALUES (:tipo, :finalidade, :titulo, :descricao, :valor, :area, :quartos, :banheiros, :vagas, :cidade, :bairro, :status, :foto)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tipo',       $imovel->tipo);
        $stmt->bindParam(':finalidade', $imovel->finalidade);
        $stmt->bindParam(':titulo',     $imovel->titulo);
        $stmt->bindParam(':descricao',  $imovel->descricao);
        $stmt->bindParam(':valor',      $valor);
        $stmt->bindParam(':area',       $area);
        $stmt->bindParam(':quartos',    $quartos);
        $stmt->bindParam(':banheiros',  $banheiros);
        $stmt->bindParam(':vagas',      $vagas);
        $stmt->bindParam(':cidade',     $imovel->cidade);
        $stmt->bindParam(':bairro',     $imovel->bairro);
        $stmt->bindParam(':status',     $status);
        $stmt->bindParam(':foto',       $imovel->foto);
        return $stmt->execute();
    }

    public function atualizar(Imovel $imovel) {
        $tiposValidos       = ['casa', 'apartamento', 'chacara', 'terreno', 'sitio', 'empresarial'];
        $finalidadesValidas = ['alugar', 'comprar', 'financiamento'];
        $statusValidos      = ['disponivel', 'vendido', 'alugado'];

        if (!in_array($imovel->tipo, $tiposValidos) || !in_array($imovel->finalidade, $finalidadesValidas) || !in_array($imovel->status, $statusValidos)) {
            return false;
        }

        $id        = (int)$imovel->id;
        $valor     = abs((float)$imovel->valor);
        $area      = abs((float)$imovel->area);
        $quartos   = abs((int)$imovel->quartos);
        $banheiros = abs((int)$imovel->banheiros);
        $vagas     = abs((int)$imovel->vagas);

        $sql = "UPDATE {$this->tabela} SET tipo=:tipo, finalidade=:finalidade, titulo=:titulo, descricao=:descricao,
                valor=:valor, area=:area, quartos=:quartos, banheiros=:banheiros, vagas=:vagas,
                cidade=:cidade, bairro=:bairro, status=:status, foto=:foto WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tipo',       $imovel->tipo);
        $stmt->bindParam(':finalidade', $imovel->finalidade);
        $stmt->bindParam(':titulo',     $imovel->titulo);
        $stmt->bindParam(':descricao',  $imovel->descricao);
        $stmt->bindParam(':valor',      $valor);
        $stmt->bindParam(':area',       $area);
        $stmt->bindParam(':quartos',    $quartos);
        $stmt->bindParam(':banheiros',  $banheiros);
        $stmt->bindParam(':vagas',      $vagas);
        $stmt->bindParam(':cidade',     $imovel->cidade);
        $stmt->bindParam(':bairro',     $imovel->bairro);
        $stmt->bindParam(':status',     $imovel->status);
        $stmt->bindParam(':foto',       $imovel->foto);
        $stmt->bindParam(':id',         $id);
        return $stmt->execute();
    }

    public function atualizarStatus($id, $status) {
        $id  = (int)$id;
        $sql = "UPDATE {$this->tabela} SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id',     $id);
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

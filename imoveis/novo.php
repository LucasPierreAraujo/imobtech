<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/ImovelDAO.php';

$db  = new Database();
$dao = new ImovelDAO($db->conectar());
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imovel             = new Imovel();
    $imovel->tipo       = $_POST['tipo'];
    $imovel->finalidade = $_POST['finalidade'];
    $imovel->titulo     = $_POST['titulo'];
    $imovel->descricao  = $_POST['descricao'];
    $imovel->valor      = $_POST['valor'];
    $imovel->area       = $_POST['area'];
    $imovel->quartos    = $_POST['quartos'];
    $imovel->banheiros  = $_POST['banheiros'];
    $imovel->vagas      = $_POST['vagas'];
    $imovel->cidade     = $_POST['cidade'];
    $imovel->bairro     = $_POST['bairro'];
    $imovel->foto       = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $imagemInfo = @getimagesize($_FILES['foto']['tmp_name']);
        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if ($imagemInfo && in_array($imagemInfo['mime'], $mimesPermitidos)) {
            $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext  = $extensoes[$imagemInfo['mime']];
            $nome = uniqid('imovel_') . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../uploads/' . $nome);
            $imovel->foto = $nome;
        } else {
            $erro = 'Arquivo inválido. Envie apenas imagens JPG, PNG ou WEBP.';
        }
    }

    if (!$erro && $dao->criar($imovel)) {
        header('Location: index.php?msg=Imóvel cadastrado com sucesso!');
        exit;
    } elseif (!$erro) {
        $erro = 'Erro ao cadastrar imóvel.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h3 class="mb-3">Novo Imóvel</h3>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Selecione</option>
                        <option value="casa">Casa</option>
                        <option value="apartamento">Apartamento</option>
                        <option value="chacara">Chácara</option>
                        <option value="terreno">Terreno</option>
                        <option value="sitio">Sítio</option>
                        <option value="empresarial">Empresarial</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Finalidade</label>
                    <select name="finalidade" class="form-select" required>
                        <option value="">Selecione</option>
                        <option value="alugar">Alugar</option>
                        <option value="comprar">Comprar</option>
                        <option value="financiamento">Financiamento</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Valor (R$)</label>
                    <input type="number" step="0.01" name="valor" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Área (m²)</label>
                    <input type="number" step="0.01" name="area" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Quartos</label>
                    <input type="number" name="quartos" class="form-control" value="0">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Banheiros</label>
                    <input type="number" name="banheiros" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Vagas de Garagem</label>
                    <input type="number" name="vagas" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Cidade</label>
                    <input type="text" name="cidade" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Bairro</label>
                    <input type="text" name="bairro" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Foto do imóvel</label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Imovel.php';
require_once __DIR__ . '/../classes/ImovelDAO.php';

$db  = new Database();
$dao = new ImovelDAO($db->conectar());

$dados = $dao->buscarPorId((int)($_GET['id'] ?? 0));
if (!$dados) { header('Location: index.php'); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imovel             = new Imovel();
    $imovel->id         = (int)$_POST['id'];
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
    $imovel->status     = $_POST['status'];
    $imovel->foto       = $dados['foto'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $imagemInfo      = @getimagesize($_FILES['foto']['tmp_name']);
        $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if ($imagemInfo && in_array($imagemInfo['mime'], $mimesPermitidos)) {
            $fotoConteudo = file_get_contents($_FILES['foto']['tmp_name']);
            $imovel->foto = 'data:' . $imagemInfo['mime'] . ';base64,' . base64_encode($fotoConteudo);
        } else {
            $erro = 'Arquivo inválido. Envie apenas imagens JPG, PNG ou WEBP.';
        }
    }

    if (!$erro && $dao->atualizar($imovel)) {
        header('Location: index.php?msg=' . urlencode('Imóvel atualizado com sucesso!'));
        exit;
    } elseif (!$erro) {
        $erro = 'Erro ao atualizar imóvel.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<h3 class="mb-3">Editar Imóvel</h3>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $dados['id'] ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Título</label>
                    <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($dados['titulo']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <?php foreach (['casa', 'apartamento', 'chacara', 'terreno', 'sitio', 'empresarial'] as $t): ?>
                        <option value="<?= $t ?>" <?= $dados['tipo'] == $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Finalidade</label>
                    <select name="finalidade" class="form-select" required>
                        <option value="alugar" <?= $dados['finalidade'] === 'alugar' ? 'selected' : '' ?>>Alugar</option>
                        <option value="comprar" <?= in_array($dados['finalidade'], ['comprar', 'financiamento']) ? 'selected' : '' ?>>Comprar / Financiar</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Valor (R$)</label>
                    <input type="number" step="0.01" name="valor" class="form-control" value="<?= $dados['valor'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Área (m²)</label>
                    <input type="number" step="0.01" name="area" class="form-control" value="<?= $dados['area'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Quartos</label>
                    <input type="number" name="quartos" class="form-control" value="<?= $dados['quartos'] ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Banheiros</label>
                    <input type="number" name="banheiros" class="form-control" value="<?= $dados['banheiros'] ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Vagas</label>
                    <input type="number" name="vagas" class="form-control" value="<?= $dados['vagas'] ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($dados['cidade']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['disponivel', 'vendido', 'alugado'] as $s): ?>
                        <option value="<?= $s ?>" <?= $dados['status'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Bairro</label>
                    <input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($dados['bairro']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($dados['descricao']) ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Foto do imóvel</label>
                    <?php if ($dados['foto']): ?>
                        <div class="mb-2"><img src="<?= $dados['foto'] ?>" style="height:80px;border-radius:6px;"></div>
                    <?php endif; ?>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Deixe em branco para manter a foto atual</small>
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

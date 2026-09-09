<?php
include_once('inc/config.php');
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] == false) {
    header('location:login.php');
    exit;
}


$smt = $conexao->prepare('SELECT ds_cargo, status FROM Funcionarios WHERE ds_email = ?');
$smt->bind_param('s', $_SESSION['email']);
$smt->execute();
$cargo = $smt->get_result()->fetch_assoc();

if ($cargo['status'] != 'ativo') {
    header('location:logout.php');
    exit;
}
if ($cargo['ds_cargo'] != $_SESSION['cargo']) {
    $_SESSION['cargo'] = $cargo['ds_cargo'];
}


$smt = $conexao->prepare('SELECT cd_funcionario FROM Funcionarios WHERE ds_email = ?');
$smt->bind_param('s', $_SESSION['email']);
$smt->execute();
$func = $smt->get_result()->fetch_assoc();
$id_funcionario = $func['cd_funcionario'];


if (isset($_POST['cadastrar'])) {
    $produtos = $_POST['produto'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];

    $vl_total = 0;
    $itens_validos = [];

 
    foreach ($produtos as $i => $id_produto) {
        $qtd = (int)($quantidades[$i] ?? 0);
        if ($qtd <= 0) continue;

        $stmt = $conexao->prepare('SELECT nm_produto, vl_produto, qt_estoque FROM Produtos WHERE cd_produto = ?');
        $stmt->bind_param('i', $id_produto);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();

        if (!$prod || $prod['qt_estoque'] < $qtd) {
            $_SESSION['mensagemJs'] = "alert('Estoque insuficiente do produto: " . ($prod['nm_produto'] ?? '') . "')";
            header('location:vendas.php');
            exit;
        }

        $vl_total += $prod['vl_produto'] * $qtd;
        $itens_validos[] = [
            'id_produto' => $id_produto,
            'quantidade' => $qtd,
            'vl_unitario' => $prod['vl_produto']
        ];
    }

    if (empty($itens_validos)) {
        $_SESSION['mensagemJs'] = "alert('Selecione pelo menos um produto com quantidade')";
        header('location:vendas.php');
        exit;
    }

  
    $stmt = $conexao->prepare('INSERT INTO vendas (dt_venda, vl_total, id_funcionario) VALUES (NOW(), ?, ?)');
    $stmt->bind_param('di', $vl_total, $id_funcionario);
    $stmt->execute();
    $id_venda = $conexao->insert_id;

    foreach ($itens_validos as $item) {
        $stmt = $conexao->prepare('INSERT INTO itens_venda (id_venda, id_produto, quantidade, vl_unitario) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiid', $id_venda, $item['id_produto'], $item['quantidade'], $item['vl_unitario']);
        $stmt->execute();

        $stmt = $conexao->prepare('UPDATE Produtos SET qt_estoque = qt_estoque - ? WHERE cd_produto = ?');
        $stmt->bind_param('ii', $item['quantidade'], $item['id_produto']);
        $stmt->execute();
    }

    $_SESSION['mensagemJs'] = "alert('Venda cadastrada com sucesso!')";
    header('location:vendas.php');
    exit;
}


$sql = "SELECT v.cd_venda, v.dt_venda, v.vl_total, f.nm_funcionario
        FROM vendas v
        INNER JOIN Funcionarios f ON v.id_funcionario = f.cd_funcionario
        ORDER BY v.dt_venda DESC";
$result = $conexao->query($sql);


$produtos = $conexao->query('SELECT cd_produto, nm_produto, vl_produto, qt_estoque FROM Produtos WHERE qt_estoque > 0 ORDER BY nm_produto');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Estoque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <style>
   * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #e2e8f0;
        }

        .navbar {
            height: 65px;
            width: 100%;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #38bdf8;
        }

        .usuario {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .usuario-info {
            text-align: right;
        }

        .usuario-nome {
            font-weight: bold;
            color: #f1f5f9;
        }

        .usuario-cargo {
            font-size: 12px;
            color: #94a3b8;
        }

        .btn-sair {
            background: #ef4444;
            color: white;
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 7px;
            transition: 0.2s;
        }

        .btn-sair:hover {
            background: #dc2626;
            color: white;
        }

        .sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            width: 240px;
            height: calc(100vh - 65px);
            background: rgba(15, 23, 42, 0.95);
            border-right: 1px solid rgba(148, 163, 184, 0.1);
            padding: 20px 12px;
        }

        .menu-titulo {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            padding: 10px 15px;
            margin-bottom: 5px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 5px;
            border-radius: 7px;
            transition: 0.2s;
        }

        .menu a:hover {
            background: rgba(56, 189, 248, 0.15);
            color: #e0f2fe;
        }

        .menu a.ativo {
            background: #0ea5e9;
            color: white;
        }

        .icone {
            width: 25px;
            text-align: center;
        }

        .conteudo {
            margin-left: 240px;
            padding: 95px 30px 30px;
        }

        .cabecalho-pagina {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .cabecalho-pagina h1 {
            font-size: 28px;
            color: #f1f5f9;
        }

        .cabecalho-pagina p {
            color: #94a3b8;
            margin-top: 5px;
        }

        .btn-adicionar {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 7px;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-adicionar:hover {
            background: #0284c7;
            color: white;
        }

   
        .table {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            color: #e2e8f0;
        }

        .table thead th {
            background: rgba(15, 23, 42, 0.9) !important;
            color: #e2e8f0 !important;
            border: none;
        }

        .table td,
        .table th {
            border-color: rgba(148, 163, 184, 0.15) !important;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(56, 189, 248, 0.1) !important;
        }

     
        .modal-content {
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .modal-footer {
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        .form-control {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: #f1f5f9;
        }

        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.8);
            border-color: #0ea5e9;
            color: #f1f5f9;
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
        }

        .close {
            color: #e2e8f0;
            opacity: 0.8;
        }

        .produto-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
        }

        .produto-item input[type="number"] {
            width: 80px;
        }

        @media (max-width: 700px) {
            .sidebar {
                width: 70px;
            }

            .menu-titulo {
                display: none;
            }

            .menu a {
                justify-content: center;
            }

            .menu a span:not(.icone) {
                display: none;
            }

            .conteudo {
                margin-left: 70px;
            }

            .usuario-info {
                display: none;
            }

            .cabecalho-pagina {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">📦 Estoque</div>
        <div class="usuario">
            <div class="usuario-info">
                <div class="usuario-nome"><?= $_SESSION['nome'] ?? 'Usuário' ?></div>
                <div class="usuario-cargo"><?= $_SESSION['cargo'] ?? 'Funcionário' ?></div>
            </div>
            <a href="logout.php" class="btn-sair">Sair</a>
        </div>
    </nav>

    <aside class="sidebar">
        <div class="menu">
            <div class="menu-titulo">Menu</div>
            <a href="index.php"><span class="icone">🏠</span><span>Dashboard</span></a>
            <a href="produtos.php"><span class="icone">📦</span><span>Produtos</span></a>
            <?php if (isset($_SESSION['cargo']) && ($_SESSION['cargo'] == 'admin' || $_SESSION['cargo'] == 'estoquista')): ?>
                <a href="categorias.php"><span class="icone">🏷️</span><span>Categorias</span></a>
            <?php endif; ?>
            <a href="compras.php"><span class="icone">🛒</span><span>Compras</span></a>
            <a href="vendas.php" class="ativo"><span class="icone">💰</span><span>Vendas</span></a>
            <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 'admin'): ?>
                <a href="funcionarios.php"><span class="icone">👥</span><span>Funcionários</span></a>
            <?php endif; ?>
        </div>
    </aside>

    <main class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Vendas</h1>
                <p>Gerencie as vendas realizadas.</p>
            </div>
            <button type="button" class="btn btn-adicionar" data-toggle="modal" data-target="#modalNovaVenda">
                + Nova Venda
            </button>
        </div>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Data</th>
                    <th>Funcionário</th>
                    <th>Valor Total</th>
                    <th width="120">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($venda = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $venda['cd_venda'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($venda['dt_venda'])) ?></td>
                            <td><?= htmlspecialchars($venda['nm_funcionario']) ?></td>
                            <td>R$ <?= number_format($venda['vl_total'], 2, ',', '.') ?></td>
                            <td>
                                <a href="venda_detalhes.php?id=<?= $venda['cd_venda'] ?>" class="btn btn-info btn-sm">Ver itens</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma venda registrada ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <div class="modal fade" id="modalNovaVenda" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Venda</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                        <p class="mb-3">Selecione os produtos e informe a quantidade:</p>

                        <?php while ($p = $produtos->fetch_assoc()): ?>
                            <div class="produto-item">
                                <input type="hidden" name="produto[]" value="<?= $p['cd_produto'] ?>">
                                <div style="flex:1">
                                    <strong><?= htmlspecialchars($p['nm_produto']) ?></strong><br>
                                    <small>R$ <?= number_format($p['vl_produto'], 2, ',', '.') ?> | Estoque: <?= $p['qt_estoque'] ?></small>
                                </div>
                                <input type="number" name="quantidade[]" class="form-control" min="0" max="<?= $p['qt_estoque'] ?>" value="0" placeholder="Qtd">
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="cadastrar" class="btn btn-primary">Finalizar Venda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        <?php if (isset($_SESSION['mensagemJs'])) {
            echo $_SESSION['mensagemJs'];
            unset($_SESSION['mensagemJs']);
        } ?>
    </script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>
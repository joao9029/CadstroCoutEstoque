<?php
include_once('inc/config.php');
session_start();
if (!isset($_SESSION['login'])) {
    header('location:login.php');
    exit;
} else if ($_SESSION['login'] == false) {
    header('location:login.php');
    exit;
}


$smt = $conexao->prepare('select ds_cargo,status from Funcionarios where ds_email=? ');
$smt->bind_param('s', $_SESSION['email']);
$smt->execute();

$resultado = $smt->get_result();
$cargo = $resultado->fetch_assoc();
if ($cargo['status'] != "ativo") {
    header('location:logout.php');
    exit;
}

if ($cargo['ds_cargo'] != "admin") {
    header('location:index.php');
    exit;
}
if ($cargo['ds_cargo'] != $_SESSION['cargo']) {
    $_SESSION['cargo'] = $cargo['ds_cargo'];

}


$sql = "SELECT * FROM Funcionarios";
$result = $conexao->query($sql);


if (isset($_POST['cadastrar'])) {
    $nome = $_POST['nm_funcionario'];
    $dt_nascimento = $_POST['dt_nascimento'];
    $senha = $_POST['ds_senha'];
    $email = $_POST['ds_email'];
    $cargo = $_POST['ds_cargo'];
    $telefone = $_POST['ds_telefone'];

    $enviar = $conexao->prepare('insert into funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES (?, ?, ?, ?, ?,?)');
    $enviar->bind_param('ssssss', $nome, $dt_nascimento, $telefone, $email, $cargo, $senha);
    if ($enviar->execute()) {

        $_SESSION['mensagemJs'] = "alert('Funcionario Cadastrado com Sucesso')";
    } else {

        $_SESSION['mensagemJs'] = "alert('ERRO ao cadstrar Funcionario')";
    }
    header('location:funcionarios.php');
    exit;
}

if (isset($_POST['editar'])) {
    $nome = $_POST['nm_funcionario'];
    $dt_nascimento = $_POST['dt_nascimento'];
    $senha = $_POST['ds_senha'];
    $email = $_POST['ds_email'];
    $cargo = $_POST['ds_cargo'];
    $telefone = $_POST['ds_telefone'];
    $status = $_POST['status'];
    $id = $_POST['cd_funcionario'];

    $editar = $conexao->prepare('UPDATE funcionarios SET nm_funcionario = ?, dt_nascimento = ?, ds_telefone = ?, ds_email = ?, ds_cargo = ?, ds_senha = ?, status = ? WHERE cd_funcionario = ?');
    $editar->bind_param('sssssssi', $nome, $dt_nascimento, $telefone, $email, $cargo, $senha, $status, $id);

    if ($editar->execute()) {


        $_SESSION['mensagemJs'] = "alert('Funcionario Editado com Sucesso')";
    } else {


        $_SESSION['mensagemJs'] = "alert('ERRO ao Editar Funcionario')";
    }
    header('location:funcionarios.php');
    exit;
}


if (isset($_POST['excluir'])) {
    $id = $_POST['id'];
    $status = "inativo";
    $excluir = $conexao->prepare('UPDATE funcionarios SET status = ? where cd_funcionario=?');
    $excluir->bind_param('si', $status, $id);
    if ($excluir->execute()) {
        $_SESSION['mensagemJs'] = "alert('Funcionario Excluido com Sucesso')";
    }else{
         $_SESSION['mensagemJs'] = "alert('ERRO ao Excluir Funcionario')";
    }
    header('location:funcionarios.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários - Estoque</title>
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

        .table td, .table th {
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
                <div class="usuario-nome"><?php echo $_SESSION['nome'] ?? 'Usuário'; ?></div>
                <div class="usuario-cargo"><?php echo $_SESSION['cargo'] ?? 'Funcionário'; ?></div>
            </div>
            <a href="logout.php" class="btn-sair">Sair</a>
        </div>
    </nav>

    <aside class="sidebar">
        <div class="menu">
            <div class="menu-titulo">Menu</div>
            <a href="index.php">
                <span class="icone">🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="produtos.php">
                <span class="icone">📦</span>
                <span>Produtos</span>
            </a>
            <?php if (isset($_SESSION['cargo']) && ($_SESSION['cargo'] == 'admin' || $_SESSION['cargo'] == 'estoquista')): ?>
                <a href="categorias.php">
                    <span class="icone">🏷️</span>
                    <span>Categorias</span>
                </a>
            <?php endif; ?>
            <a href="compras.php">
                <span class="icone">🛒</span>
                <span>Compras</span>
            </a>
            <a href="vendas.php">
                <span class="icone">💰</span>
                <span>Vendas</span>
            </a>
            <?php if (isset($_SESSION['cargo']) && $_SESSION['cargo'] == 'admin'): ?>
                <a href="funcionarios.php" class="ativo">
                    <span class="icone">👥</span>
                    <span>Funcionários</span>
                </a>
            <?php endif; ?>
        </div>
    </aside>

    <main class="conteudo">
        <div class="cabecalho-pagina">
            <div>
                <h1>Funcionários</h1>
                <p>Gerencie os funcionários do sistema.</p>
            </div>
            <button type="button" class="btn btn-adicionar btn-sm" data-toggle="modal" data-target="#modalNovoFuncionario">
                + Novo Funcionário
            </button>
        </div>

        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Nome</th>
                    <th>Data nascimento</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Status</th>
                    <th>Senha</th>
                    <th width="180">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($linha = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($linha['cd_funcionario']) ?></td>
                            <td><?= htmlspecialchars($linha['nm_funcionario']) ?></td>
                            <td><?= htmlspecialchars($linha['dt_nascimento']) ?></td>
                            <td><?= htmlspecialchars($linha['ds_telefone']) ?></td>
                            <td><?= htmlspecialchars($linha['ds_email']) ?></td>
                            <td><?= htmlspecialchars($linha['ds_cargo']) ?></td>
                            <td><?= htmlspecialchars($linha['status']) ?></td>
                            <td><?= htmlspecialchars($linha['ds_senha']) ?></td>
                            <td>
                                <div class="d-flex">
                                    <form action="funcionarios.php" method="post" class="mr-2">
                                        <input type="hidden" name="id" value="<?= $linha['cd_funcionario'] ?>">
                                        <button type="submit" name="excluir" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                                    </form>
                                    <button type="button" class="btn btn-warning btn-editar btn-sm"
                                        data-id="<?= $linha['cd_funcionario'] ?>"
                                        data-nome="<?= htmlspecialchars($linha['nm_funcionario']) ?>"
                                        data-nascimento="<?= $linha['dt_nascimento'] ?>"
                                        data-telefone="<?= htmlspecialchars($linha['ds_telefone']) ?>"
                                        data-email="<?= htmlspecialchars($linha['ds_email']) ?>"
                                        data-cargo="<?= $linha['ds_cargo'] ?>"
                                        data-status="<?= $linha['status'] ?>"
                                        data-toggle="modal" data-target="#modalEditarFuncionario">
                                        Editar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">Nenhum funcionário cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <div class="modal fade" id="modalNovoFuncionario" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="funcionarios.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo funcionário</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" class="form-control" name="nm_funcionario" required>
                        </div>
                        <div class="form-group">
                            <label>Data de nascimento</label>
                            <input type="date" class="form-control" name="dt_nascimento" required>
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" class="form-control" name="ds_telefone" placeholder="(11) 99999-9999">
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" class="form-control" name="ds_email" required>
                        </div>
                        <div class="form-group">
                            <label>Cargo</label>
                            <select class="form-control" name="ds_cargo" required>
                                <option value="">Selecione um cargo</option>
                                <option value="admin">Administrador</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="estoquista">Estoquista</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" class="form-control" name="ds_senha" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="cadastrar" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

 
    <div class="modal fade" id="modalEditarFuncionario" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="funcionarios.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar funcionário</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="cd_funcionario" id="editar_id">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" class="form-control" name="nm_funcionario" id="editar_nome" required>
                        </div>
                        <div class="form-group">
                            <label>Data de nascimento</label>
                            <input type="date" class="form-control" name="dt_nascimento" id="editar_nascimento" required>
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" class="form-control" name="ds_telefone" id="editar_telefone">
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" class="form-control" name="ds_email" id="editar_email" required>
                        </div>
                        <div class="form-group">
                            <label>Cargo</label>
                            <select class="form-control" name="ds_cargo" id="editar_cargo" required>
                                <option value="admin">Administrador</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="estoquista">Estoquista</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="editar_status" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nova senha</label>
                            <input type="password" class="form-control" name="ds_senha" placeholder="Deixe vazio para manter a senha atual">
                            <small class="text-muted">Só preencha se quiser alterar a senha.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editar" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const botoesEditar = document.querySelectorAll(".btn-editar");
            botoesEditar.forEach(function (botao) {
                botao.addEventListener("click", function () {
                    document.getElementById("editar_id").value = this.dataset.id;
                    document.getElementById("editar_nome").value = this.dataset.nome;
                    document.getElementById("editar_nascimento").value = this.dataset.nascimento;
                    document.getElementById("editar_telefone").value = this.dataset.telefone;
                    document.getElementById("editar_email").value = this.dataset.email;
                    document.getElementById("editar_cargo").value = this.dataset.cargo;
                    document.getElementById("editar_status").value = this.dataset.status;
                });
            });
        });
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
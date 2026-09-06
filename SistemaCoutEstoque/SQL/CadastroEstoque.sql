create database Estoque;
use Estoque;

create table Produtos(
cd_produto int unique primary key auto_increment not null,
nm_produto varchar(80) not null,
vl_produto decimal(10,2) not null,
dt_validade_produto date not null,
ds_produto varchar(255) not null,
ds_preco double not null
);

create table Categorias(
cd_categoria int unique primary key auto_increment not null,
nm_categoria varchar(80) not null,
ds_categoria varchar(255),
status enum('ativo', 'inativo') default 'ativo'
);

create table Funcionarios (
cd_funcionario int unique primary key auto_increment not null,
nm_funcionario varchar(100) not null,
dt_nascimento date not null,
ds_setor varchar(50),
ds_telefone varchar(20),
ds_email varchar(100),
ds_cargo enum('admin', 'vendedor', 'estoquista','gerente') default 'vendedor',
status enum('ativo', 'inativo') default 'ativo'
);

create table Login(
cd_login int unique primary key auto_increment not null,
nm_usuario varchar(100) not null,
ds_email varchar(255) not null,
ds_senha varchar(155) not null,
id_funcionario int not null,
status enum('ativo', 'inativo') default 'ativo',
foreign key (id_funcionario) references Funcionarios(cd_funcionario)
);

create table Compras(
cd_compra int unique primary key auto_increment not null,
vl_compra decimal(10,2) not null,
id_produto int not null
);

create table Vendas (
cd_venda int unique primary key auto_increment not null,
id_funcionario int not null,
dt_venda datetime not null,
vl_total decimal(10,2) not null,
foreign key (id_funcionario) references Funcionarios(cd_funcionario)
);

create table produto_categoria (
id_produto_categoria int unique primary key auto_increment not null,
id_produto int not null,
id_categoria int not null,
foreign key (id_produto) references Produtos(cd_produto),
foreign key (id_categoria) references Categorias(cd_categoria)
);

create table itens_compra (
id_item_compra int unique primary key auto_increment not null,
id_compra int not null,
id_produto int not null,
qt_produto int not null,
vl_unitario decimal(10,2) not null,
foreign key (id_compra) references Compras(cd_compra),
foreign key (id_produto) references Produtos(cd_produto)
);

create table itens_venda (
id_item_venda int unique primary key auto_increment not null,
id_venda int not null,
id_produto int not null,
quantidade int not null,
vl_unitario decimal(10,2) not null,
foreign key (id_venda) references Vendas(cd_venda),
foreign key (id_produto) references Produtos(cd_produto)
);

select p.nm_produto, c.nm_categoria
from produtos p
inner join produto_categoria pc on p.cd_produto = pc.id_produto
inner join categorias c on pc.id_categoria = c.cd_categoria;

select c.cd_compra, p.nm_produto, qt_produto, ic.vl_unitario
from compras c
inner join itens_compra ic on c.cd_compra = ic.id_compra
inner join produtos p on ic.id_produto = p.cd_produto;

create database Estoque;
use Estoque;
-- possivel tabela estoque
-- possivel tabela fornecedor
-- possivel tabela cliente

-- os produtos, o basico tipo comida, limpeza, etc,
create table Produtos(
cd_produto int primary key auto_increment not null,
nm_produto varchar(80) not null,
vl_produto decimal(10,2) not null,
dt_validade_produto date not null,
ds_produto varchar(255) not null

);

create table fornecedores (
    cd_fornecedor int primary key auto_increment not null,
    nm_fornecedor varchar(100) not null,
    ds_telefone varchar(20) not null,
    ds_email varchar(100) not null
);

-- e na onde os produtos vão fica, pq n ´pode deixar bagunçado, exemplo(categorias: comida, limpeza, higiene, congelador
create table Categorias(
cd_categoria int  primary key auto_increment not null,
nm_categoria varchar(80) not null,
ds_categoria varchar(255),
status enum('ativo', 'inativo') default 'ativo'
);

-- aqui n preciso falar nada, da pra saber
create table Funcionarios (
cd_funcionario int  primary key auto_increment not null,
nm_funcionario varchar(100) not null,
dt_nascimento date not null,
ds_telefone varchar(20),
ds_email varchar(100),
ds_cargo enum('admin', 'vendedor', 'estoquista') default 'vendedor',
status enum('ativo', 'inativo') default 'ativo',
ds_senha varchar(155) not null
);


create table compras (
    cd_compra int primary key auto_increment not null,
    dt_compra datetime not null,
    vl_total decimal(10,2) not null,
    id_funcionario int not null,
    foreign key (id_funcionario) references funcionarios(cd_funcionario)
);

-- saida de produtos do estoque
create table vendas (
    cd_venda int primary key auto_increment not null,
    dt_venda datetime not null,
    vl_total decimal(10,2) not null,
    id_funcionario int not null,
    foreign key (id_funcionario) references funcionarios(cd_funcionario)
);

create table produto_categoria (
id_produto_categoria int  primary key auto_increment not null,
id_produto int not null,
id_categoria int not null,
foreign key (id_produto) references Produtos(cd_produto),
foreign key (id_categoria) references Categorias(cd_categoria)
);

create table itens_compra (
id_item_compra int  primary key auto_increment not null,
id_compra int not null,
id_produto int not null,
qt_produto int not null,
vl_unitario decimal(10,2) not null,
foreign key (id_compra) references Compras(cd_compra),
foreign key (id_produto) references Produtos(cd_produto)
);

create table itens_venda (
id_item_venda int  primary key auto_increment not null,
id_venda int not null,
id_produto int not null,
quantidade int not null,
vl_unitario decimal(10,2) not null,
foreign key (id_venda) references Vendas(cd_venda),
foreign key (id_produto) references Produtos(cd_produto)
);

-- inner join
select p.nm_produto, c.nm_categoria
from produtos p
inner join produto_categoria pc on p.cd_produto = pc.id_produto
inner join categorias c on pc.id_categoria = c.cd_categoria;

select c.cd_compra, p.nm_produto, qt_produto, ic.vl_unitario
from compras c
inner join itens_compra ic on c.cd_compra = ic.id_compra
inner join produtos p on ic.id_produto = p.cd_produto;


-- admin
insert into Funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES ("JoãoLucas", "03/07/2010", "23904-123", "joao.aura@empresario.com", "admin", "123456");
insert into Funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES ("IsaqueSevero", "21/09/2009", "12345-777", "Isaque.severo@empresario.com", "admin", "123456");

-- gerente
insert into Funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES ("JoãoLucas", "03/07/2010", "23904-123", "joao.gerente@empresario.com", "vendedor", "123456");
insert into Funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES ("IsaqueSevero", "21/09/2009", "12345-777", "isaque.gerente@empresario.com", "vendedor", "123456");

-- estoquista
insert into Funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES ("JoãoLucas", "03/07/2010", "23904-123", "joao.CLT@empresario.com", "estoquista", "123456");
insert into Funcionarios (nm_funcionario, dt_nascimento, ds_telefone, ds_email, ds_cargo, ds_senha) 
VALUES ("IsaqueSevero", "21/09/2009", "12345-777", "isaque.CLT@empresario.com", "estoquista", "123456");


-- Produtos
-- Produtos (com quantidade)
insert into produtos (nm_produto, ds_produto, vl_produto, qt_estoque, id_categoria) values
-- categoria de gamer 1)
('Mouse Gamer', 'Mouse com RGB', 89.90, 100, 1),
('Teclado Mecânico', 'Teclado aurudo', 249.90, 100, 1),
('Monitor (6000 hrz)', 'Roda a sua vida', 2490.90, 50, 1),
('PC ultra Gamer', 'PC dos sonhos', 24900.90, 10, 1),
('', 'Teclado switch blue', 249.90, 30, 1),

-- categoria de eletronicos
('', 'Teclado switch blue', 249.90, 30, 2),
('', 'Teclado switch blue', 249.90, 30, 2),
('', 'Teclado switch blue', 249.90, 30, 2),
('', 'Teclado switch blue', 249.90, 30, 2),
('', 'Teclado switch blue', 249.90, 30, 2),

-- categoria de alimento (id 2)
('Arroz 5kg', 'Arroz tipo 1', 24.90, 100, 3),
('Arroz 5kg', 'Arroz tipo 1', 24.90, 100, 3),
('Arroz 5kg', 'Arroz tipo 1', 24.90, 100, 3),
('Arroz 5kg', 'Arroz tipo 1', 24.90, 100, 3),
('Arroz 5kg', 'Arroz tipo 1', 24.90, 100, 3),

-- categoria de limpeza (id)
('Detergente', 'Detergente 500ml', 3.50, 200, 4),
('Detergente', 'Detergente 500ml', 3.50, 200, 4);

	   

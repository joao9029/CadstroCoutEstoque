create database Estoque;
use Estoque;

create table Produtos(
cd_produto int unique primary key auto_increment not null,
nm_produto varchar(80) not null,
vl_produto decimal(4.7) not null,
dt_validade_produto date not null,
ds_produto varchar(255) not null,
ds_preco double not null
);

create table Login(
cd_login int unique primary key auto_increment not null,

);

create table Compras(
cd_produto int unique primary key auto_increment not null,
vl_compra decimal(6.7) not null,
id_produto int not null
);

create table Vendas (
cd_produto int unique primary key auto_increment not null,

);

create table Usuários (
cd_produto int unique primary key auto_increment not null,

);




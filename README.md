# Backend API (Laravel)

Este é o backend do Projeto Integrador, desenvolvido utilizando o framework PHP **Laravel**.

## O que é Laravel?

Laravel é um framework PHP livre e de código aberto para o desenvolvimento de sistemas web. Ele possui uma sintaxe elegante e expressiva, projetado para facilitar tarefas comuns em projetos como autenticação, roteamento, sessões e cache. O Laravel foca na experiência do desenvolvedor, oferecendo ferramentas poderosas e abstraindo a complexidade de rotinas comuns do dia a dia.

## Ponto Inicial e Rotas

- **Ponto Inicial da Aplicação:** O arquivo de entrada para todas as requisições é o `public/index.php`. Este arquivo carrega o framework e processa a requisição do usuário. A inicialização real do framework fica em `bootstrap/app.php`.
- **Rotas:** O mapeamento das URLs da aplicação pode ser encontrado dentro da pasta `routes/`.
  - **`routes/api.php`**: Contém as rotas para a API (sem estado, autenticadas via tokens, geralmente retornando JSON).
  - **`routes/web.php`**: Contém as rotas web tradicionais (com suporte a cookies e sessão).

---

## Como rodar o projeto usando Docker

O projeto possui um ambiente Docker configurado (via `Dockerfile` e `docker-compose.yml`) que inclui a aplicação e um banco de dados MySQL.

1. **Subir a infraestrutura:**
   Abra o terminal na pasta `backend` e execute:
   ```bash
   docker-compose up -d --build
   ```
   *Esse comando constrói a imagem e inicia os containers em segundo plano. Os containers criados serão o `backend_app` (aplicação) e o `db_app` (MySQL).*

2. **Acessar o container e preparar o projeto:**
   Se for a primeira vez rodando, é recomendável acessar o container para instalar dependências e rodar as migrações:
   ```bash
   docker exec -it backend_app bash
   # Dentro do container, execute:
   composer setup
   ```
   *(Opcional) O comando `composer setup` já cuida do `composer install`, `.env`, `key:generate` e migrações.*

A aplicação ficará disponível na porta 8000: `http://localhost:8000`

---

## Como rodar o projeto localmente (Na Máquina)

Caso não deseje utilizar Docker, é necessário ter o **PHP (>= 8.3)**, **Composer**, **Node.js** e o **MySQL** instalados em sua máquina.

1. **Preparação automática do ambiente:**
   Este projeto possui um script customizado no Composer para configurar o básico. No terminal, execute:
   ```bash
   composer setup
   ```
   Esse script automaticamente:
   - Roda `composer install`.
   - Copia o `.env.example` para `.env` (certifique-se de configurar as credenciais do banco de dados no arquivo `.env` gerado).
   - Gera a chave da aplicação (`php artisan key:generate`).
   - Roda as migrações de banco (`php artisan migrate`).
   - Instala as dependências Node e compila o front/assets (`npm install` e `npm run build`).

2. **Iniciar o servidor de desenvolvimento:**
   Após tudo configurado, suba o servidor local utilizando:
   ```bash
   composer dev
   ```
   *Ou o comando clássico do Laravel:*
   ```bash
   php artisan serve
   ```

A aplicação ficará disponível em `http://localhost:8000` ou outra porta informada no terminal.

---

## Principais Comandos (Artisan)

O Artisan é a interface de linha de comando (CLI) do Laravel. Alguns dos comandos mais úteis são:

- `php artisan serve`: Inicia um servidor PHP de desenvolvimento local.
- `php artisan list`: Mostra todos os comandos Artisan disponíveis.
- `php artisan make:controller NomeController`: Cria um controlador.
- `php artisan make:model NomeModel -m`: Cria uma model já com seu arquivo de migration.
- `php artisan migrate`: Roda as migrações (cria as tabelas no banco de dados).
- `php artisan migrate:rollback`: Desfaz a última migração.
- `php artisan route:list`: Lista todas as rotas registradas e ativas na aplicação.
- `php artisan tinker`: Abre um terminal interativo (REPL) para rodar código PHP e testar a base de dados em tempo real.

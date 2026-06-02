# Transaction API

API REST de transações financeiras entre carteiras de usuários, desenvolvida como desafio técnico OMNI.

![CI](https://github.com/GaGuarGo/transaction-api/actions/workflows/ci.yml/badge.svg)

---

## Sumário

- [Tecnologias](#tecnologias)
- [Arquitetura](#arquitetura)
- [Estrutura de pastas](#estrutura-de-pastas)
- [Banco de dados](#banco-de-dados)
- [Endpoints](#endpoints)
- [Segurança](#segurança)
- [Como rodar](#como-rodar)
- [Testes](#testes)

---

## Tecnologias

| | |
|---|---|
| **Linguagem** | PHP 8.4 |
| **Framework** | Laravel 13 |
| **Autenticação** | Laravel Sanctum (token Bearer, expira em 1h) |
| **Banco de dados** | MySQL 8.4 |
| **Testes** | PHPUnit 12 |
| **Linter** | Laravel Pint |
| **Containerização** | Docker + Docker Compose |
| **CI** | GitHub Actions |

---

## Arquitetura

O projeto segue **Clean Architecture**, separando responsabilidades em camadas independentes. A regra principal é que camadas internas nunca dependem de camadas externas.

```
┌─────────────────────────────────────────────┐
│                  Http Layer                 │  ← Controllers, Requests, Resources, Routes
├─────────────────────────────────────────────┤
│              Infrastructure                 │  ← Eloquent Repositories, Models
├─────────────────────────────────────────────┤
│              Application Layer              │  ← Use Cases, DTOs, Exceptions
├─────────────────────────────────────────────┤
│                Domain Layer                 │  ← Entities, Repository Interfaces
└─────────────────────────────────────────────┘
```

### Domain Layer (`app/Domain/`)

Núcleo da aplicação — não depende de nenhum framework. Contém:

- **Entities** — objetos imutáveis que representam o estado do domínio (`User`, `Wallet`, `Transaction`)
- **Repository Interfaces** — contratos que a infraestrutura deve implementar

### Application Layer (`app/Application/`)

Orquestra casos de uso. Depende apenas do Domain. Contém:

- **Use Cases** — uma classe por operação (`SignUpUseCase`, `TransferUseCase`, etc.)
- **DTOs** — objetos de transferência de dados entre camadas
- **Exceptions** — exceções de domínio tipadas (`InsufficientBalanceException`, `WalletNotFoundException`, etc.)

### Infrastructure Layer (`app/Infrastructure/`)

Implementações concretas dos contratos do domínio usando Eloquent. Os bindings são registrados no `AppServiceProvider`.

### Http Layer (`app/Http/`)

Ponto de entrada da aplicação. Responsável por:

- Receber e validar requests (Form Requests)
- Chamar o Use Case correspondente
- Autorizar via Policies
- Retornar a resposta formatada (Resources)

---

## Estrutura de pastas

```
app/
├── Domain/
│   ├── User/
│   │   ├── Entities/User.php
│   │   └── Repositories/UserRepositoryInterface.php
│   ├── Wallet/
│   │   ├── Entities/Wallet.php
│   │   └── Repositories/WalletRepositoryInterface.php
│   └── Transaction/
│       ├── Entities/Transaction.php
│       └── Repositories/TransactionRepositoryInterface.php
├── Application/
│   ├── UseCases/
│   │   ├── User/        (SignUp, SignIn, ListUsers, UpdateUser, DeleteUser)
│   │   ├── Wallet/      (CreateWallet, ListUserWallets, GetWallet)
│   │   └── Transaction/ (Transfer, GetTransactionHistory)
│   ├── DTOs/
│   └── Exceptions/
├── Infrastructure/
│   └── Repositories/
│       ├── EloquentUserRepository.php
│       ├── EloquentWalletRepository.php
│       └── EloquentTransactionRepository.php
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/              (Eloquent models — apenas infra)
├── Policies/
│   └── WalletPolicy.php
└── Providers/
    └── AppServiceProvider.php
```

---

## Banco de dados

Todos os IDs são **UUID v4**. O saldo (`balance`) é armazenado em **centavos** (integer) para evitar problemas de ponto flutuante.

```
users
├── id          uuid  PK
├── username    string  unique
├── email       string  unique
├── password    string
├── birthdate   date
├── created_at
├── updated_at
└── deleted_at  (soft delete)

wallets
├── id          uuid  PK
├── user_id     uuid  FK → users.id
├── name        string  (default: "default")
├── balance     bigint unsigned  (em centavos)
├── created_at
└── updated_at

transactions
├── id                  uuid  PK
├── sender_wallet_id    uuid  FK → wallets.id
├── receiver_wallet_id  uuid  FK → wallets.id
├── amount              bigint unsigned  (em centavos)
└── created_at
```

### Concorrência e integridade de saldo

Transferências usam **Pessimistic Locking** (`SELECT FOR UPDATE`) para garantir que transações simultâneas na mesma carteira não se sobreponham. As carteiras são sempre bloqueadas em ordem consistente (UUID menor primeiro) para prevenir deadlocks.

---

## Endpoints

Todos os endpoints retornam JSON. Rotas protegidas exigem `Authorization: Bearer <token>`.

### Auth

| Método | Rota | Descrição | Auth |
|---|---|---|---|
| `POST` | `/api/users/signup` | Cadastro de usuário | — |
| `POST` | `/api/users/signin` | Login, retorna token | — |
| `POST` | `/api/auth/refresh` | Renova o token | ✓ |
| `POST` | `/api/auth/logout` | Invalida o token | ✓ |
| `POST` | `/api/auth/validate` | Verifica se o token é válido | ✓ |

### Usuários

| Método | Rota | Descrição | Auth |
|---|---|---|---|
| `GET` | `/api/users` | Lista todos os usuários | ✓ |
| `PUT` | `/api/users/me` | Atualiza dados do usuário autenticado | ✓ |
| `DELETE` | `/api/users/me` | Exclui a conta do usuário autenticado | ✓ |
| `GET` | `/api/users/me/transactions` | Histórico de transações (`?walletId=` opcional) | ✓ |

### Carteiras

| Método | Rota | Descrição | Auth |
|---|---|---|---|
| `GET` | `/api/wallets` | Lista carteiras do usuário autenticado | ✓ |
| `POST` | `/api/wallets` | Cria uma nova carteira | ✓ |
| `GET` | `/api/wallets/{id}` | Detalhes de uma carteira | ✓ |

### Transações

| Método | Rota | Descrição | Auth |
|---|---|---|---|
| `POST` | `/api/transfer` | Transfere entre carteiras | ✓ |

> A coleção completa no formato OpenAPI está em [`docs/openapi.json`](docs/openapi.json) — importe no ApiDog ou Insomnia.

---

## Segurança

- **Rate limiting** — signup e signin limitados a 10 req/min por IP
- **Senha forte** — mínimo 8 caracteres, letras maiúsculas, minúsculas e números
- **Tokens com expiração** — Sanctum com TTL de 1h; revogados automaticamente no logout e na exclusão de conta
- **Policies** — `WalletPolicy` garante que o usuário só acessa e transfere de carteiras próprias
- **Soft delete** — contas excluídas não são removidas fisicamente do banco
- **Locking pessimista** — transferências usam `SELECT FOR UPDATE` para evitar race conditions

---

## Como rodar

### Com Docker

```bash
# Subir containers (app + db + db_test)
docker compose up -d

# Gerar app key e rodar migrations
docker compose exec app cp .env.docker .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

A API estará disponível em `http://localhost:8000/api`.

### Sem Docker (local)

```bash
composer install

cp .env.example .env
php artisan key:generate

# Configure DB_* no .env e rode:
php artisan migrate

php artisan serve
```

---

## Testes

O projeto possui dois tipos de teste:

- **Unit tests** — testam os Use Cases isoladamente com repositórios mockados
- **Integration tests** — testam os endpoints HTTP completos com banco de dados real (MySQL via Docker ou SQLite em memória)

```bash
# Rodar todos os testes
php artisan test

# Rodar apenas unit tests
php artisan test --testsuite=Unit

# Rodar apenas integration tests
php artisan test --testsuite=Integration
```

O CI roda automaticamente em todo push/PR via GitHub Actions, executando Pint (linter) e PHPUnit em paralelo.

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
php artisan test

# Run only unit tests
php artisan test --testsuite=Unit

# Run only integration tests
php artisan test --testsuite=Integration

# Run a single test file
php artisan test tests/Unit/UseCases/User/SignUpUseCaseTest.php

# Lint (Laravel Pint)
./vendor/bin/pint

# Lint check only (no changes)
./vendor/bin/pint --test

# Start dev server
php artisan serve
```

Integration tests require a `.env.testing` file with DB credentials (separate MySQL test DB or SQLite in-memory). Docker provides a `db_test` container for this purpose.

## Architecture

This project follows **Clean Architecture** with strict layer separation. Inner layers never depend on outer layers.

### Layer flow
```
Http (Controllers/Requests/Resources)
  → Application (UseCases/DTOs/Exceptions)
    → Domain (Entities/Repository Interfaces)
      ← Infrastructure (Eloquent Repositories — implements Domain interfaces)
```

### Key rules
- **Domain layer** (`app/Domain/`) has zero framework dependencies — pure PHP entities and repository interfaces.
- **Application layer** (`app/Application/`) contains one Use Case class per operation. Use Cases receive domain repository interfaces via constructor injection.
- **Infrastructure layer** (`app/Infrastructure/Repositories/`) contains Eloquent implementations of repository interfaces. Bindings are registered in `AppServiceProvider`.
- **Eloquent Models** (`app/Models/`) are used only by the Infrastructure layer — never directly in Use Cases or Controllers.
- **Controllers** receive a Use Case via constructor injection, call a Form Request for validation, and return a Resource.

### Domain concepts
- All IDs are **UUID v4** strings.
- Wallet `balance` is stored in **centavos (integer)** — never floats.
- `Transfer` uses **pessimistic locking** (`SELECT FOR UPDATE`), locking wallets in consistent UUID order to prevent deadlocks.
- Users support **soft deletes**; tokens are revoked on logout and account deletion.

### Repository binding pattern
New repository interfaces must be bound in `AppServiceProvider::register()`:
```php
$this->app->bind(SomethingRepositoryInterface::class, EloquentSomethingRepository.php);
```

### Authorization
`WalletPolicy` (registered via `Gate::policy` in `AppServiceProvider`) enforces that users can only access and transfer from their own wallets.

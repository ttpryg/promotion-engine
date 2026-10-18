# Changelog

All notable changes to `ttpryg/promotion-engine` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- `PromotionRepositoryInterface::incrementUsageCount()` performing an atomic, limit-guarded increment of `usage_count`.
- `PromotionUsageLimitExceededException` thrown when a promotion's global usage limit is reached during usage recording.
- `TransactionManagerInterface` with `PdoTransactionManager` (PDO-backed ACID transactions) and `NullTransactionManager` (no-op default) implementations.
- Optional `$transactionManager` argument on the `PromotionService` constructor.

### Changed
- `PromotionService::recordPromotionUsage()` now uses the atomic `incrementUsageCount()` instead of a read-modify-write cycle, preventing race conditions and overselling during simultaneous checkouts.
- `PromotionService::recordPromotionUsage()` wraps the usage counter increment and usage history insert in a single database transaction, rolling both back on failure; `PromotionUsedEvent` is dispatched only after a successful commit.

### Planned (Production Readiness Improvements)
- **Database Indexing**: Add database indices on `promotions(code)`, `promotions(store_id, is_active)`, and composite index on `promotion_usages(promotion_id, user_id)`.
- **Domain Validation & Exceptions**: Introduce `InvalidPromotionException` and guard clauses for percentage bounds, negative amounts, and invalid date ranges.
- **Priority Resolution**: Prioritize store-scoped coupons over platform-wide global coupons in `findByCode`.

---

## [1.0.1] - 2026-09-22

### Added
- Rector configuration (`rector.php`) with code quality rules and automated refactoring scripts (`composer fix`, `composer fix:check`).
- Dedicated PHP configuration (`.docker/php/php.ini`) inside Docker container.

### Changed
- Enforced strict types and improved variable naming conventions across entity and repository classes.
- Updated Dockerfile to support user permissions (`www-data`) and configured Git safe directories.
- Refactored `PromotionServiceTest` and `PromotionEvaluatorTest` to enforce explicit `PromotionType` parameters.
- Updated Composer dependencies and automated code styling via Laravel Pint.

---

## [1.0.0] - 2026-09-20

### Added
- **Core Entities & Enums**:
  - `Promotion` entity supporting `FIXED_AMOUNT` and `PERCENTAGE` discount types.
  - `PromotionUsage` entity for tracking usage per user, cart, and order.
  - `PromotionType` enum.
- **Evaluation Engine**:
  - `DefaultPromotionEvaluator` evaluating minimum spend thresholds, date windows, global usage limits, user usage limits, and store scoping.
  - `DiscountResult` DTO for eligible and ineligible outcomes.
- **Services**:
  - `PromotionService` orchestrating promotion creation, coupon evaluation, automatic storewide promotion lookup, and usage recording.
- **Storage Repositories**:
  - `PdoPromotionRepository` & `PdoPromotionUsageRepository` for relational database persistence (MariaDB, MySQL, SQLite).
  - `MemoryPromotionRepository` & `MemoryPromotionUsageRepository` for high-speed in-memory unit testing.
- **Domain Events (PSR-14)**:
  - `PromotionCreatedEvent`, `PromotionAppliedEvent`, and `PromotionUsedEvent`.
- **Database Schema**:
  - Relational schema definitions in `database/schema.sql`.
- **Testing & Tooling**:
  - PHPUnit 10 test suite covering unit and SQLite in-memory integration tests.
  - GitHub Actions CI workflow supporting PHP 8.1, 8.2, and 8.3.
  - Docker Compose setup with Nginx and PHP 8.1 FPM.

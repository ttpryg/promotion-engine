<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Database\DatabaseMigrator;
use Ttpryg\PromotionEngine\Database\MigrationRunner;

class DatabaseMigratorTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function test_fresh_install_creates_schema_and_baselines_migrations(): void
    {
        (new DatabaseMigrator($this->pdo))->migrate();

        $this->assertTrue($this->tableExists('promotions'));
        $this->assertTrue($this->tableExists('promotion_usages'));
        $this->assertTrue($this->indexExists('idx_promotions_code'));
        $this->assertTrue($this->indexExists('idx_usages_promo_user'));

        $applied = (new MigrationRunner($this->pdo))->appliedMigrations();
        $this->assertContains('001_add_promotion_indexes', $applied);
    }

    public function test_migration_runner_adds_indexes_to_legacy_database(): void
    {
        $this->createLegacyTables();
        $this->assertFalse($this->indexExists('idx_promotions_code'));

        $executed = (new MigrationRunner($this->pdo))->migrate();

        $this->assertContains('001_add_promotion_indexes', $executed);
        $this->assertTrue($this->indexExists('idx_promotions_code'));
        $this->assertTrue($this->indexExists('idx_promotions_store_active'));
        $this->assertTrue($this->indexExists('idx_usages_promo_user'));
        $this->assertTrue($this->indexExists('idx_usages_order_id'));
    }

    public function test_migration_runner_is_idempotent(): void
    {
        $this->createLegacyTables();
        $runner = new MigrationRunner($this->pdo);

        $this->assertContains('001_add_promotion_indexes', $runner->migrate());
        $this->assertSame([], $runner->migrate());
    }

    public function test_database_migrator_is_idempotent(): void
    {
        $migrator = new DatabaseMigrator($this->pdo);

        $migrator->migrate();
        $migrator->migrate();

        $this->assertSame(1, count((new MigrationRunner($this->pdo))->appliedMigrations()));
    }

    public function test_existing_database_gets_upgraded_without_losing_data(): void
    {
        $this->createLegacyTables();
        $this->pdo->exec("INSERT INTO promotions (id, code, store_id, is_active) VALUES ('p1', 'LEGACY', 'store-1', 1)");

        (new DatabaseMigrator($this->pdo))->migrate();

        $this->assertTrue($this->indexExists('idx_promotions_code'));
        $this->assertSame(1, $this->countRows('promotions'));
    }

    private function createLegacyTables(): void
    {
        $this->pdo->exec(
            'CREATE TABLE promotions (
                id VARCHAR(36) PRIMARY KEY,
                code VARCHAR(50) NULL,
                store_id VARCHAR(36) NULL,
                is_active TINYINT(1) DEFAULT 1
            );
            CREATE TABLE promotion_usages (
                id VARCHAR(36) PRIMARY KEY,
                promotion_id VARCHAR(36) NULL,
                user_id VARCHAR(36) NULL,
                order_id VARCHAR(36) NULL
            );'
        );
    }

    private function tableExists(string $table): bool
    {
        return $this->sqliteObjectExists('table', $table);
    }

    private function indexExists(string $index): bool
    {
        return $this->sqliteObjectExists('index', $index);
    }

    private function sqliteObjectExists(string $type, string $name): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sqlite_master WHERE type = :type AND name = :name');
        $stmt->execute(['type' => $type, 'name' => $name]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function countRows(string $table): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }
}

<?php

namespace Ttpryg\PromotionEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;

class PdoSchemaIndexTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__.'/../../database/schema.sql');
        $this->pdo->exec($schema);
    }

    /**
     * @dataProvider expectedIndexProvider
     */
    public function test_schema_defines_expected_indexes(string $indexName): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'index' AND name = :name");
        $stmt->execute(['name' => $indexName]);

        $this->assertSame(1, (int) $stmt->fetchColumn(), "Missing index: {$indexName}");
    }

    public static function expectedIndexProvider(): array
    {
        return [
            'promotions.code' => ['idx_promotions_code'],
            'promotions store + active' => ['idx_promotions_store_active'],
            'usages promotion + user' => ['idx_usages_promo_user'],
            'usages order' => ['idx_usages_order_id'],
        ];
    }
}

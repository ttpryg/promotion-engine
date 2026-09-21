<?php

namespace Ttpryg\PromotionEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Entities\Promotion;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionRepository;

class PdoPromotionRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoPromotionRepository $pdoPromotionRepository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__.'/../../database/schema.sql');
        $this->pdo->exec($schema);

        $this->pdoPromotionRepository = new PdoPromotionRepository($this->pdo);
    }

    public function test_save_and_find_by_code(): void
    {
        $promotion = new Promotion(
            id: 'promo-sqlite-1',
            code: 'SQLITE20',
            name: 'Diskon SQLite',
            description: 'Test Deskripsi',
            type: PromotionType::PERCENTAGE,
            value: 20.0,
            storeId: 'store-abc',
            ownerId: 'owner-xyz',
            minSpend: 50000.0,
            maxDiscount: 10000.0
        );

        $this->pdoPromotionRepository->save($promotion);

        $fetched = $this->pdoPromotionRepository->findByCode('SQLITE20', 'store-abc');

        $this->assertNotNull($fetched);
        $this->assertEquals('promo-sqlite-1', $fetched->id);
        $this->assertEquals('Diskon SQLite', $fetched->name);
        $this->assertEquals(PromotionType::PERCENTAGE, $fetched->type);
        $this->assertEquals(20.0, $fetched->value);
        $this->assertEquals('store-abc', $fetched->storeId);
    }
}

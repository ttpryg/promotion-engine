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

    public function test_code_exists_is_scoped_to_exact_store(): void
    {
        $this->pdoPromotionRepository->save(new Promotion(
            id: 'promo-scope-store',
            code: 'SCOPED',
            name: 'Store Coupon',
            description: null,
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            storeId: 'store-1'
        ));

        $this->assertTrue($this->pdoPromotionRepository->codeExists('scoped', 'store-1'));
        $this->assertFalse($this->pdoPromotionRepository->codeExists('scoped', 'store-2'));
        $this->assertFalse($this->pdoPromotionRepository->codeExists('scoped'));
        $this->assertFalse($this->pdoPromotionRepository->codeExists('scoped', 'store-1', 'promo-scope-store'));
    }

    public function test_increment_usage_count_respects_global_limit(): void
    {
        $promotion = new Promotion(
            id: 'promo-limit-1',
            code: 'LIMITED',
            name: 'Kupon Terbatas',
            description: null,
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            usageLimit: 2
        );

        $this->pdoPromotionRepository->save($promotion);

        $this->assertTrue($this->pdoPromotionRepository->incrementUsageCount('promo-limit-1'));
        $this->assertTrue($this->pdoPromotionRepository->incrementUsageCount('promo-limit-1'));
        $this->assertFalse($this->pdoPromotionRepository->incrementUsageCount('promo-limit-1'));

        $this->assertSame(2, $this->pdoPromotionRepository->findById('promo-limit-1')?->usageCount);
    }

    public function test_increment_usage_count_returns_false_for_unknown_promotion(): void
    {
        $this->assertFalse($this->pdoPromotionRepository->incrementUsageCount('missing-promo'));
    }

    public function test_increment_usage_count_without_limit_is_unbounded(): void
    {
        $promotion = new Promotion(
            id: 'promo-unlimited',
            code: 'UNLIMITED',
            name: 'Kupon Tanpa Batas',
            description: null,
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0
        );

        $this->pdoPromotionRepository->save($promotion);

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($this->pdoPromotionRepository->incrementUsageCount('promo-unlimited'));
        }

        $this->assertSame(5, $this->pdoPromotionRepository->findById('promo-unlimited')?->usageCount);
    }
}

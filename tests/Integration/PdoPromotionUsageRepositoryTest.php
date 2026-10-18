<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Entities\Promotion;
use Ttpryg\PromotionEngine\Entities\PromotionUsage;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionUsageRepository;

class PdoPromotionUsageRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoPromotionUsageRepository $pdoPromotionUsageRepository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__.'/../../database/schema.sql');
        $this->pdo->exec($schema);

        (new PdoPromotionRepository($this->pdo))->save(new Promotion(
            id: 'promo-usage-1',
            code: 'USAGE1',
            name: 'Kupon Usage',
            description: null,
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0
        ));

        $this->pdoPromotionUsageRepository = new PdoPromotionUsageRepository($this->pdo);
    }

    public function test_record_usage_persists_all_fields(): void
    {
        $this->pdoPromotionUsageRepository->recordUsage(new PromotionUsage(
            id: 'usage-1',
            promotionId: 'promo-usage-1',
            userId: 'user-1',
            discountAmount: 10000.0,
            cartId: 'cart-1',
            orderId: 'order-1'
        ));

        $stmt = $this->pdo->prepare('SELECT * FROM promotion_usages WHERE id = :id');
        $stmt->execute(['id' => 'usage-1']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertSame('promo-usage-1', $row['promotion_id']);
        $this->assertSame('user-1', $row['user_id']);
        $this->assertSame('cart-1', $row['cart_id']);
        $this->assertSame('order-1', $row['order_id']);
        $this->assertEquals(10000.0, (float) $row['discount_amount']);
    }

    public function test_count_user_usage_counts_only_matching_rows(): void
    {
        $this->record('usage-a', 'user-1');
        $this->record('usage-b', 'user-1');
        $this->record('usage-c', 'user-2');

        $this->assertSame(2, $this->pdoPromotionUsageRepository->countUserUsage('promo-usage-1', 'user-1'));
        $this->assertSame(1, $this->pdoPromotionUsageRepository->countUserUsage('promo-usage-1', 'user-2'));
    }

    public function test_count_user_usage_returns_zero_when_none_recorded(): void
    {
        $this->assertSame(0, $this->pdoPromotionUsageRepository->countUserUsage('promo-usage-1', 'unknown-user'));
    }

    private function record(string $usageId, string $userId): void
    {
        $this->pdoPromotionUsageRepository->recordUsage(new PromotionUsage(
            id: $usageId,
            promotionId: 'promo-usage-1',
            userId: $userId,
            discountAmount: 10000.0
        ));
    }
}

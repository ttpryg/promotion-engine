<?php

namespace Ttpryg\PromotionEngine\Tests\Integration;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionUsageRepository;
use Ttpryg\PromotionEngine\Services\PromotionService;
use Ttpryg\PromotionEngine\Transactions\PdoTransactionManager;

class PromotionServiceTransactionTest extends TestCase
{
    private PDO $pdo;

    private PdoPromotionRepository $promotionRepository;

    private PromotionService $promotionService;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__.'/../../database/schema.sql');
        $this->pdo->exec($schema);

        $this->promotionRepository = new PdoPromotionRepository($this->pdo);

        $this->promotionService = new PromotionService(
            $this->promotionRepository,
            new PdoPromotionUsageRepository($this->pdo),
            transactionManager: new PdoTransactionManager($this->pdo)
        );
    }

    public function test_usage_recording_commits_both_increment_and_history(): void
    {
        $this->promotionService->createPromotion(
            id: 'tx-promo-1',
            name: 'Kupon Transaksi',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            code: 'TXCE',
            usageLimit: 5
        );

        $this->promotionService->recordPromotionUsage(
            usageId: 'tx-usage-1',
            promotionId: 'tx-promo-1',
            userId: 'user-1',
            discountAmount: 10000.0
        );

        $this->assertSame(1, $this->promotionRepository->findById('tx-promo-1')?->usageCount);
        $this->assertSame(1, $this->countUsageRows('tx-promo-1'));
    }

    public function test_usage_recording_rolls_back_increment_when_history_insert_fails(): void
    {
        $this->promotionService->createPromotion(
            id: 'tx-promo-2',
            name: 'Kupon Rollback',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            code: 'TXRB',
            usageLimit: 5
        );

        $this->promotionService->recordPromotionUsage(
            usageId: 'duplicate-usage',
            promotionId: 'tx-promo-2',
            userId: 'user-1',
            discountAmount: 10000.0
        );

        $this->assertSame(1, $this->promotionRepository->findById('tx-promo-2')?->usageCount);

        try {
            $this->promotionService->recordPromotionUsage(
                usageId: 'duplicate-usage',
                promotionId: 'tx-promo-2',
                userId: 'user-2',
                discountAmount: 10000.0
            );
            $this->fail('Expected a duplicate primary key violation.');
        } catch (PDOException) {
            // Expected: unique PK violation triggers a rollback of the increment.
        }

        $this->assertFalse($this->pdo->inTransaction());
        $this->assertSame(1, $this->promotionRepository->findById('tx-promo-2')?->usageCount);
        $this->assertSame(1, $this->countUsageRows('tx-promo-2'));
    }

    private function countUsageRows(string $promotionId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM promotion_usages WHERE promotion_id = :promotion_id');
        $stmt->execute(['promotion_id' => $promotionId]);

        return (int) $stmt->fetchColumn();
    }
}

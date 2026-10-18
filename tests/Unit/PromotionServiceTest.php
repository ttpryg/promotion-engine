<?php

namespace Ttpryg\PromotionEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Exceptions\PromotionUsageLimitExceededException;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionUsageRepository;
use Ttpryg\PromotionEngine\Services\PromotionService;

class PromotionServiceTest extends TestCase
{
    private PromotionService $promotionService;

    private MemoryPromotionRepository $memoryPromotionRepository;

    private MemoryPromotionUsageRepository $memoryPromotionUsageRepository;

    protected function setUp(): void
    {
        $this->memoryPromotionRepository = new MemoryPromotionRepository;
        $this->memoryPromotionUsageRepository = new MemoryPromotionUsageRepository;
        $this->promotionService = new PromotionService($this->memoryPromotionRepository, $this->memoryPromotionUsageRepository);
    }

    public function test_create_and_evaluate_coupon(): void
    {
        $this->promotionService->createPromotion(
            id: 'p1',
            name: 'Voucher Merdeka',
            type: PromotionType::PERCENTAGE,
            value: 17.0,
            code: 'MERDEKA17',
            storeId: 'store-100',
            minSpend: 50000.0
        );

        $discountResult = $this->promotionService->evaluateCoupon('MERDEKA17', [
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-100',
        ]);

        $this->assertTrue($discountResult->isEligible);
        $this->assertEquals(17000.0, $discountResult->discountAmount);
        $this->assertEquals('MERDEKA17', $discountResult->code);
    }

    public function test_record_usage_increments_usage_count(): void
    {
        $this->promotionService->createPromotion(
            id: 'p2',
            name: 'Kupon 1x Pakai',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            code: 'ONECE',
            userUsageLimit: 1
        );

        $this->promotionService->recordPromotionUsage(
            usageId: 'u1',
            promotionId: 'p2',
            userId: 'user-777',
            discountAmount: 10000.0,
            cartId: 'cart-123'
        );

        // Second evaluation for same user should fail due to user usage limit
        $discountResult = $this->promotionService->evaluateCoupon('ONECE', [
            'cart_subtotal' => 50000.0,
            'user_id' => 'user-777',
        ]);

        $this->assertFalse($discountResult->isEligible);
        $this->assertStringContainsString('limit reached', strtolower($discountResult->reason));
    }

    public function test_store_scoped_coupon_takes_priority_over_global(): void
    {
        // Global is registered first to prove insertion order does not matter.
        $this->promotionService->createPromotion(
            id: 'g1',
            name: 'Global 10%',
            type: PromotionType::PERCENTAGE,
            value: 10.0,
            code: 'PRIO'
        );

        $this->promotionService->createPromotion(
            id: 's1',
            name: 'Store 20%',
            type: PromotionType::PERCENTAGE,
            value: 20.0,
            code: 'PRIO',
            storeId: 'store-1'
        );

        $storeResult = $this->promotionService->evaluateCoupon('PRIO', [
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-1',
        ]);

        $this->assertTrue($storeResult->isEligible);
        $this->assertEquals(20000.0, $storeResult->discountAmount);

        $globalResult = $this->promotionService->evaluateCoupon('PRIO', [
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-2',
        ]);

        $this->assertTrue($globalResult->isEligible);
        $this->assertEquals(10000.0, $globalResult->discountAmount);
    }

    public function test_record_usage_throws_when_global_limit_exceeded(): void
    {
        $this->promotionService->createPromotion(
            id: 'p3',
            name: 'Kupon Global 1x',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: 'GLOBAL1',
            usageLimit: 1
        );

        $this->promotionService->recordPromotionUsage(
            usageId: 'u3-1',
            promotionId: 'p3',
            userId: 'user-1',
            discountAmount: 5000.0
        );

        $this->expectException(PromotionUsageLimitExceededException::class);

        try {
            $this->promotionService->recordPromotionUsage(
                usageId: 'u3-2',
                promotionId: 'p3',
                userId: 'user-2',
                discountAmount: 5000.0
            );
        } finally {
            $this->assertSame(1, $this->memoryPromotionRepository->findById('p3')?->usageCount);
        }
    }
}

<?php

namespace Ttpryg\PromotionEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Enums\PromotionType;
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
            minSpend: 50000.0,
            storeId: 'store-100'
        );

        $result = $this->promotionService->evaluateCoupon('MERDEKA17', [
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-100',
        ]);

        $this->assertTrue($result->isEligible);
        $this->assertEquals(17000.0, $result->discountAmount);
        $this->assertEquals('MERDEKA17', $result->code);
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
        $result = $this->promotionService->evaluateCoupon('ONECE', [
            'cart_subtotal' => 50000.0,
            'user_id' => 'user-777',
        ]);

        $this->assertFalse($result->isEligible);
        $this->assertStringContainsString('limit reached', strtolower($result->reason));
    }
}

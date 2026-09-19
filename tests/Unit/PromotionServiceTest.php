<?php

namespace Ttpryg\PromotionEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionUsageRepository;
use Ttpryg\PromotionEngine\Services\PromotionService;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;
    private MemoryPromotionRepository $promotionRepo;
    private MemoryPromotionUsageRepository $usageRepo;

    protected function setUp(): void
    {
        $this->promotionRepo = new MemoryPromotionRepository();
        $this->usageRepo = new MemoryPromotionUsageRepository();
        $this->service = new PromotionService($this->promotionRepo, $this->usageRepo);
    }

    public function testCreateAndEvaluateCoupon(): void
    {
        $this->service->createPromotion(
            id: 'p1',
            name: 'Voucher Merdeka',
            type: PromotionType::PERCENTAGE,
            value: 17.0,
            code: 'MERDEKA17',
            minSpend: 50000.0,
            storeId: 'store-100'
        );

        $result = $this->service->evaluateCoupon('MERDEKA17', [
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-100'
        ]);

        $this->assertTrue($result->isEligible);
        $this->assertEquals(17000.0, $result->discountAmount);
        $this->assertEquals('MERDEKA17', $result->code);
    }

    public function testRecordUsageIncrementsUsageCount(): void
    {
        $promo = $this->service->createPromotion(
            id: 'p2',
            name: 'Kupon 1x Pakai',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            code: 'ONECE',
            userUsageLimit: 1
        );

        $this->service->recordPromotionUsage(
            usageId: 'u1',
            promotionId: 'p2',
            userId: 'user-777',
            discountAmount: 10000.0,
            cartId: 'cart-123'
        );

        // Second evaluation for same user should fail due to user usage limit
        $result = $this->service->evaluateCoupon('ONECE', [
            'cart_subtotal' => 50000.0,
            'user_id' => 'user-777'
        ]);

        $this->assertFalse($result->isEligible);
        $this->assertStringContainsString('limit reached', strtolower($result->reason));
    }
}

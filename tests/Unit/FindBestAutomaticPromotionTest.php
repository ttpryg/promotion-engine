<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionUsageRepository;
use Ttpryg\PromotionEngine\Services\PromotionService;

class FindBestAutomaticPromotionTest extends TestCase
{
    private PromotionService $promotionService;

    protected function setUp(): void
    {
        $this->promotionService = new PromotionService(
            new MemoryPromotionRepository,
            new MemoryPromotionUsageRepository
        );
    }

    public function test_returns_best_discount_among_automatic_promotions(): void
    {
        $this->promotionService->createPromotion(
            id: 'auto-small',
            name: 'Diskon 10rb',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0
        );

        $this->promotionService->createPromotion(
            id: 'auto-big',
            name: 'Diskon 15rb',
            type: PromotionType::FIXED_AMOUNT,
            value: 15000.0
        );

        $result = $this->promotionService->findBestAutomaticPromotion(['cart_subtotal' => 100000.0]);

        $this->assertNotNull($result);
        $this->assertTrue($result->isEligible);
        $this->assertSame(15000.0, $result->discountAmount);
        $this->assertSame('auto-big', $result->promotionId);
    }

    public function test_ignores_code_based_coupons(): void
    {
        $this->promotionService->createPromotion(
            id: 'coupon-big',
            name: 'Kupon 90%',
            type: PromotionType::PERCENTAGE,
            value: 90.0,
            code: 'BIG90'
        );

        $this->promotionService->createPromotion(
            id: 'auto-only',
            name: 'Diskon 5rb',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0
        );

        $result = $this->promotionService->findBestAutomaticPromotion(['cart_subtotal' => 100000.0]);

        $this->assertNotNull($result);
        $this->assertSame('auto-only', $result->promotionId);
        $this->assertSame(5000.0, $result->discountAmount);
    }

    public function test_returns_null_when_only_coupons_exist(): void
    {
        $this->promotionService->createPromotion(
            id: 'coupon-only',
            name: 'Kupon 10%',
            type: PromotionType::PERCENTAGE,
            value: 10.0,
            code: 'ONLY10'
        );

        $this->assertNull(
            $this->promotionService->findBestAutomaticPromotion(['cart_subtotal' => 100000.0])
        );
    }

    public function test_returns_null_when_no_promotion_matches_context(): void
    {
        $this->promotionService->createPromotion(
            id: 'auto-min-spend',
            name: 'Diskon Min 500rb',
            type: PromotionType::FIXED_AMOUNT,
            value: 50000.0,
            minSpend: 500000.0
        );

        $this->assertNull(
            $this->promotionService->findBestAutomaticPromotion(['cart_subtotal' => 100000.0])
        );
    }

    public function test_respects_user_usage_limit_for_automatic_promotions(): void
    {
        $this->promotionService->createPromotion(
            id: 'auto-once',
            name: 'Diskon Sekali Pakai',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0,
            userUsageLimit: 1
        );

        $this->promotionService->recordPromotionUsage(
            usageId: 'usage-auto-1',
            promotionId: 'auto-once',
            userId: 'user-1',
            discountAmount: 10000.0
        );

        $this->assertNull(
            $this->promotionService->findBestAutomaticPromotion([
                'cart_subtotal' => 100000.0,
                'user_id' => 'user-1',
            ])
        );

        $result = $this->promotionService->findBestAutomaticPromotion([
            'cart_subtotal' => 100000.0,
            'user_id' => 'user-2',
        ]);

        $this->assertNotNull($result);
        $this->assertTrue($result->isEligible);
    }

    public function test_scopes_automatic_promotions_to_store(): void
    {
        $this->promotionService->createPromotion(
            id: 'auto-global',
            name: 'Diskon Global 10rb',
            type: PromotionType::FIXED_AMOUNT,
            value: 10000.0
        );

        $this->promotionService->createPromotion(
            id: 'auto-store',
            name: 'Diskon Store 20rb',
            type: PromotionType::FIXED_AMOUNT,
            value: 20000.0,
            storeId: 'store-1'
        );

        $storeResult = $this->promotionService->findBestAutomaticPromotion([
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-1',
        ]);
        $this->assertNotNull($storeResult);
        $this->assertSame('auto-store', $storeResult->promotionId);

        $otherStoreResult = $this->promotionService->findBestAutomaticPromotion([
            'cart_subtotal' => 100000.0,
            'store_id' => 'store-2',
        ]);
        $this->assertNotNull($otherStoreResult);
        $this->assertSame('auto-global', $otherStoreResult->promotionId);
    }
}

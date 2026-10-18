<?php

namespace Ttpryg\PromotionEngine\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Exceptions\InvalidPromotionException;
use Ttpryg\PromotionEngine\Exceptions\PromotionNotFoundException;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\MemoryPromotionUsageRepository;
use Ttpryg\PromotionEngine\Services\PromotionService;

class PromotionValidationTest extends TestCase
{
    private PromotionService $promotionService;

    protected function setUp(): void
    {
        $this->promotionService = new PromotionService(
            new MemoryPromotionRepository,
            new MemoryPromotionUsageRepository
        );
    }

    public function test_percentage_above_one_hundred_is_rejected(): void
    {
        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-high',
            name: 'Diskon Tidak Masuk Akal',
            type: PromotionType::PERCENTAGE,
            value: 120.0
        );
    }

    public function test_percentage_below_lower_bound_is_rejected(): void
    {
        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-low',
            name: 'Diskon Nol',
            type: PromotionType::PERCENTAGE,
            value: 0.0
        );
    }

    public function test_negative_fixed_amount_is_rejected(): void
    {
        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-negative',
            name: 'Diskon Negatif',
            type: PromotionType::FIXED_AMOUNT,
            value: -5000.0
        );
    }

    public function test_negative_min_spend_is_rejected(): void
    {
        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-minspend',
            name: 'Min Spend Negatif',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            minSpend: -1.0
        );
    }

    public function test_start_after_end_date_is_rejected(): void
    {
        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-dates',
            name: 'Rentang Tanggal Salah',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            startAt: new DateTimeImmutable('2026-12-31 00:00:00'),
            endAt: new DateTimeImmutable('2026-01-01 00:00:00')
        );
    }

    public function test_empty_coupon_code_is_rejected(): void
    {
        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-empty-code',
            name: 'Kode Kosong',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: '   '
        );
    }

    public function test_duplicate_code_within_same_store_is_rejected(): void
    {
        $this->promotionService->createPromotion(
            id: 'p-dup-1',
            name: 'Kupon Store',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: 'STORE10',
            storeId: 'store-1'
        );

        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-dup-2',
            name: 'Kupon Store Duplikat',
            type: PromotionType::FIXED_AMOUNT,
            value: 7000.0,
            code: 'store10',
            storeId: 'store-1'
        );
    }

    public function test_duplicate_code_in_different_store_is_allowed(): void
    {
        $this->promotionService->createPromotion(
            id: 'p-store-1',
            name: 'Kupon Store 1',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: 'SHARED',
            storeId: 'store-1'
        );

        $promotion = $this->promotionService->createPromotion(
            id: 'p-store-2',
            name: 'Kupon Store 2',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: 'SHARED',
            storeId: 'store-2'
        );

        $this->assertSame('p-store-2', $promotion->id);
    }

    public function test_duplicate_global_code_is_rejected(): void
    {
        $this->promotionService->createPromotion(
            id: 'p-global-1',
            name: 'Kupon Global',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: 'GLOBAL5'
        );

        $this->expectException(InvalidPromotionException::class);

        $this->promotionService->createPromotion(
            id: 'p-global-2',
            name: 'Kupon Global Duplikat',
            type: PromotionType::FIXED_AMOUNT,
            value: 5000.0,
            code: 'GLOBAL5'
        );
    }

    public function test_record_usage_for_unknown_promotion_throws_not_found(): void
    {
        $this->expectException(PromotionNotFoundException::class);

        $this->promotionService->recordPromotionUsage(
            usageId: 'usage-missing',
            promotionId: 'does-not-exist',
            userId: 'user-1',
            discountAmount: 1000.0
        );
    }
}

<?php

namespace Ttpryg\PromotionEngine\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Entities\Promotion;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Evaluators\DefaultPromotionEvaluator;

class PromotionEvaluatorTest extends TestCase
{
    private DefaultPromotionEvaluator $defaultPromotionEvaluator;

    protected function setUp(): void
    {
        $this->defaultPromotionEvaluator = new DefaultPromotionEvaluator;
    }

    public function test_evaluates_fixed_amount_discount(): void
    {
        $promotion = new Promotion(
            id: 'promo-1',
            code: 'FIXED50',
            name: 'Diskon 50rb',
            description: null,
            type: PromotionType::FIXED_AMOUNT,
            value: 50000.0,
            minSpend: 100000.0
        );

        $context = ['cart_subtotal' => 150000.0];
        $result = $this->defaultPromotionEvaluator->evaluate($promotion, $context);

        $this->assertTrue($result->isEligible);
        $this->assertEquals(50000.0, $result->discountAmount);
    }

    public function test_evaluates_percentage_discount_with_max_cap(): void
    {
        $promotion = new Promotion(
            id: 'promo-2',
            code: 'DISCOUNT20',
            name: 'Diskon 20%',
            description: null,
            type: PromotionType::PERCENTAGE,
            value: 20.0,
            maxDiscount: 30000.0
        );

        $context = ['cart_subtotal' => 200000.0]; // 20% of 200k = 40k, capped at 30k
        $result = $this->defaultPromotionEvaluator->evaluate($promotion, $context);

        $this->assertTrue($result->isEligible);
        $this->assertEquals(30000.0, $result->discountAmount);
    }

    public function test_rejects_when_min_spend_not_met(): void
    {
        $promotion = new Promotion(
            id: 'promo-3',
            code: 'MIN200',
            name: 'Diskon Belanja 200rb',
            description: null,
            type: PromotionType::FIXED_AMOUNT,
            value: 25000.0,
            minSpend: 200000.0
        );

        $context = ['cart_subtotal' => 100000.0];
        $result = $this->defaultPromotionEvaluator->evaluate($promotion, $context);

        $this->assertFalse($result->isEligible);
        $this->assertEquals(0.0, $result->discountAmount);
    }

    public function test_rejects_expired_promotion(): void
    {
        $promotion = new Promotion(
            id: 'promo-4',
            code: 'EXPIRED',
            name: 'Promo Lampau',
            description: null,
            type: PromotionType::PERCENTAGE,
            value: 10.0,
            startAt: new DateTimeImmutable('-10 days'),
            endAt: new DateTimeImmutable('-1 day')
        );

        $result = $this->defaultPromotionEvaluator->evaluate($promotion, ['cart_subtotal' => 500000.0]);

        $this->assertFalse($result->isEligible);
        $this->assertStringContainsString('expired', strtolower($result->reason));
    }
}

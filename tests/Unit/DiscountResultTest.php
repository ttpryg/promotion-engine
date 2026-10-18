<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\DTO\DiscountResult;

class DiscountResultTest extends TestCase
{
    public function test_constructor_rounds_discount_amount_to_two_decimals(): void
    {
        $result = new DiscountResult(isEligible: true, discountAmount: 12.3456);

        $this->assertSame(12.35, $result->discountAmount);
    }

    public function test_eligible_factory_rounds_discount_amount(): void
    {
        $result = DiscountResult::eligible(10.0059);

        $this->assertSame(10.01, $result->discountAmount);
    }

    public function test_ineligible_result_has_zero_discount(): void
    {
        $result = DiscountResult::ineligible('nope');

        $this->assertFalse($result->isEligible);
        $this->assertSame(0.0, $result->discountAmount);
    }
}

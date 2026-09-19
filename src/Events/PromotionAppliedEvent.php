<?php

namespace Ttpryg\PromotionEngine\Events;

use Ttpryg\PromotionEngine\DTO\DiscountResult;
use Ttpryg\PromotionEngine\Entities\Promotion;

class PromotionAppliedEvent
{
    public function __construct(
        public readonly Promotion $promotion,
        public readonly DiscountResult $result,
        public readonly array $context
    ) {}
}

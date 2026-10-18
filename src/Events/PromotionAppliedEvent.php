<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Events;

use Ttpryg\PromotionEngine\DTO\DiscountResult;
use Ttpryg\PromotionEngine\Entities\Promotion;

class PromotionAppliedEvent
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly Promotion $promotion,
        public readonly DiscountResult $result,
        public readonly array $context
    ) {}
}

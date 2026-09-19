<?php

namespace Ttpryg\PromotionEngine\Contracts;

use Ttpryg\PromotionEngine\DTO\DiscountResult;
use Ttpryg\PromotionEngine\Entities\Promotion;

interface PromotionEvaluatorInterface
{
    /**
     * Evaluate if a promotion applies to the given context and calculate discount amount.
     * $context can include: cart_subtotal, user_id, user_usage_count, store_id, items, etc.
     */
    public function evaluate(Promotion $promotion, array $context): DiscountResult;
}

<?php

namespace Ttpryg\PromotionEngine\Evaluators;

use Ttpryg\PromotionEngine\Contracts\PromotionEvaluatorInterface;
use Ttpryg\PromotionEngine\DTO\DiscountResult;
use Ttpryg\PromotionEngine\Entities\Promotion;
use Ttpryg\PromotionEngine\Enums\PromotionType;

class DefaultPromotionEvaluator implements PromotionEvaluatorInterface
{
    public function evaluate(Promotion $promotion, array $context): DiscountResult
    {
        if (!$promotion->isActive) {
            return DiscountResult::ineligible('Promotion is inactive');
        }

        if ($promotion->isExpired()) {
            return DiscountResult::ineligible('Promotion is expired or not started yet');
        }

        if ($promotion->hasReachedGlobalUsageLimit()) {
            return DiscountResult::ineligible('Promotion global usage limit reached');
        }

        $userUsageCount = $context['user_usage_count'] ?? 0;
        if ($promotion->userUsageLimit !== null && $userUsageCount >= $promotion->userUsageLimit) {
            return DiscountResult::ineligible('User usage limit reached for this promotion');
        }

        $storeId = $context['store_id'] ?? null;
        if ($promotion->storeId !== null && $promotion->storeId !== $storeId) {
            return DiscountResult::ineligible('Promotion does not belong to this store');
        }

        $subtotal = (float)($context['cart_subtotal'] ?? 0.0);
        if ($subtotal < $promotion->minSpend) {
            return DiscountResult::ineligible(sprintf('Minimum spend requirement of %.2f not met', $promotion->minSpend));
        }

        $discount = 0.0;
        if ($promotion->type === PromotionType::FIXED_AMOUNT) {
            $discount = min($promotion->value, $subtotal);
        } elseif ($promotion->type === PromotionType::PERCENTAGE) {
            $discount = ($subtotal * $promotion->value) / 100.0;
            if ($promotion->maxDiscount !== null && $promotion->maxDiscount > 0) {
                $discount = min($discount, $promotion->maxDiscount);
            }
            $discount = min($discount, $subtotal);
        }

        return DiscountResult::eligible(
            discountAmount: $discount,
            promotionId: $promotion->id,
            code: $promotion->code
        );
    }
}

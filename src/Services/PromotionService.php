<?php

namespace Ttpryg\PromotionEngine\Services;

use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Ttpryg\PromotionEngine\Contracts\PromotionEvaluatorInterface;
use Ttpryg\PromotionEngine\Contracts\PromotionRepositoryInterface;
use Ttpryg\PromotionEngine\Contracts\PromotionUsageRepositoryInterface;
use Ttpryg\PromotionEngine\DTO\DiscountResult;
use Ttpryg\PromotionEngine\Entities\Promotion;
use Ttpryg\PromotionEngine\Entities\PromotionUsage;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Evaluators\DefaultPromotionEvaluator;
use Ttpryg\PromotionEngine\Events\PromotionAppliedEvent;
use Ttpryg\PromotionEngine\Events\PromotionCreatedEvent;
use Ttpryg\PromotionEngine\Events\PromotionUsedEvent;

class PromotionService
{
    private PromotionEvaluatorInterface $evaluator;

    public function __construct(
        private readonly PromotionRepositoryInterface $promotionRepo,
        private readonly PromotionUsageRepositoryInterface $usageRepo,
        ?PromotionEvaluatorInterface $evaluator = null,
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->evaluator = $evaluator ?? new DefaultPromotionEvaluator;
    }

    public function createPromotion(
        string $id,
        string $name,
        PromotionType $type,
        float $value,
        ?string $code = null,
        ?string $description = null,
        ?string $storeId = null,
        ?string $ownerId = null,
        float $minSpend = 0.0,
        ?float $maxDiscount = null,
        ?int $usageLimit = null,
        ?int $userUsageLimit = null,
        ?DateTimeImmutable $startAt = null,
        ?DateTimeImmutable $endAt = null
    ): Promotion {
        $promotion = new Promotion(
            id: $id,
            code: $code,
            name: $name,
            description: $description,
            type: $type,
            value: $value,
            storeId: $storeId,
            ownerId: $ownerId,
            minSpend: $minSpend,
            maxDiscount: $maxDiscount,
            usageLimit: $usageLimit,
            userUsageLimit: $userUsageLimit,
            startAt: $startAt,
            endAt: $endAt
        );

        $this->promotionRepo->save($promotion);
        $this->eventDispatcher?->dispatch(new PromotionCreatedEvent($promotion));

        return $promotion;
    }

    public function evaluateCoupon(string $code, array $context): DiscountResult
    {
        $storeId = $context['store_id'] ?? null;
        $promotion = $this->promotionRepo->findByCode($code, $storeId);

        if (! $promotion) {
            return DiscountResult::ineligible('Invalid or non-existent coupon code');
        }

        if (isset($context['user_id'])) {
            $context['user_usage_count'] = $this->usageRepo->countUserUsage($promotion->id, $context['user_id']);
        }

        $result = $this->evaluator->evaluate($promotion, $context);

        if ($result->isEligible) {
            $this->eventDispatcher?->dispatch(new PromotionAppliedEvent($promotion, $result, $context));
        }

        return $result;
    }

    public function findBestAutomaticPromotion(array $context): ?DiscountResult
    {
        $storeId = $context['store_id'] ?? null;
        $activePromotions = $this->promotionRepo->findActivePromotions($storeId);

        $bestResult = null;

        foreach ($activePromotions as $promotion) {
            // Automatic promotions don't require coupon codes
            if ($promotion->code !== null) {
                continue;
            }

            if (isset($context['user_id'])) {
                $context['user_usage_count'] = $this->usageRepo->countUserUsage($promotion->id, $context['user_id']);
            }

            $result = $this->evaluator->evaluate($promotion, $context);

            if ($result->isEligible) {
                if ($bestResult === null || $result->discountAmount > $bestResult->discountAmount) {
                    $bestResult = $result;
                }
            }
        }

        return $bestResult;
    }

    public function recordPromotionUsage(
        string $usageId,
        string $promotionId,
        string $userId,
        float $discountAmount,
        ?string $cartId = null,
        ?string $orderId = null
    ): PromotionUsage {
        $promotion = $this->promotionRepo->findById($promotionId);
        if ($promotion) {
            $promotion->incrementUsage();
            $this->promotionRepo->save($promotion);
        }

        $usage = new PromotionUsage(
            id: $usageId,
            promotionId: $promotionId,
            userId: $userId,
            discountAmount: $discountAmount,
            cartId: $cartId,
            orderId: $orderId
        );

        $this->usageRepo->recordUsage($usage);
        $this->eventDispatcher?->dispatch(new PromotionUsedEvent($usage));

        return $usage;
    }
}

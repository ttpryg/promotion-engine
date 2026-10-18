<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\DTO;

class DiscountResult
{
    public readonly float $discountAmount;

    public function __construct(
        public readonly bool $isEligible,
        float $discountAmount,
        public readonly ?string $reason = null,
        public readonly ?string $promotionId = null,
        public readonly ?string $code = null
    ) {
        $this->discountAmount = round($discountAmount, 2);
    }

    public static function eligible(float $discountAmount, ?string $promotionId = null, ?string $code = null): self
    {
        return new self(
            isEligible: true,
            discountAmount: $discountAmount,
            promotionId: $promotionId,
            code: $code
        );
    }

    public static function ineligible(string $reason): self
    {
        return new self(
            isEligible: false,
            discountAmount: 0.0,
            reason: $reason
        );
    }
}

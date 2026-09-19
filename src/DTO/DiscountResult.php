<?php

namespace Ttpryg\PromotionEngine\DTO;

class DiscountResult
{
    public function __construct(
        public readonly bool $isEligible,
        public readonly float $discountAmount,
        public readonly ?string $reason = null,
        public readonly ?string $promotionId = null,
        public readonly ?string $code = null
    ) {}

    public static function eligible(float $discountAmount, ?string $promotionId = null, ?string $code = null): self
    {
        return new self(
            isEligible: true,
            discountAmount: round($discountAmount, 2),
            reason: null,
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

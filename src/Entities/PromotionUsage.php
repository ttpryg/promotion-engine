<?php

namespace Ttpryg\PromotionEngine\Entities;

use DateTimeImmutable;

class PromotionUsage
{
    public function __construct(
        public readonly string $id,
        public readonly string $promotionId,
        public readonly string $userId,
        public readonly float $discountAmount,
        public readonly ?string $cartId = null,
        public readonly ?string $orderId = null,
        public ?DateTimeImmutable $usedAt = null
    ) {
        $this->usedAt = $usedAt ?? new DateTimeImmutable;
    }
}

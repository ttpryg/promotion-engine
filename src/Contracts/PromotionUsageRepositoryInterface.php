<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Contracts;

use Ttpryg\PromotionEngine\Entities\PromotionUsage;

interface PromotionUsageRepositoryInterface
{
    public function recordUsage(PromotionUsage $promotionUsage): void;

    public function countUserUsage(string $promotionId, string $userId): int;
}

<?php

namespace Ttpryg\PromotionEngine\Contracts;

use Ttpryg\PromotionEngine\Entities\PromotionUsage;

interface PromotionUsageRepositoryInterface
{
    public function recordUsage(PromotionUsage $usage): void;
    public function countUserUsage(string $promotionId, string $userId): int;
}

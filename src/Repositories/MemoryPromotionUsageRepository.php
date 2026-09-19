<?php

namespace Ttpryg\PromotionEngine\Repositories;

use Ttpryg\PromotionEngine\Contracts\PromotionUsageRepositoryInterface;
use Ttpryg\PromotionEngine\Entities\PromotionUsage;

class MemoryPromotionUsageRepository implements PromotionUsageRepositoryInterface
{
    /** @var array<string, PromotionUsage> */
    private array $usages = [];

    public function recordUsage(PromotionUsage $usage): void
    {
        $this->usages[$usage->id] = $usage;
    }

    public function countUserUsage(string $promotionId, string $userId): int
    {
        $count = 0;
        foreach ($this->usages as $usage) {
            if ($usage->promotionId === $promotionId && $usage->userId === $userId) {
                $count++;
            }
        }
        return $count;
    }
}

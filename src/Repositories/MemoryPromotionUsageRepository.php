<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Repositories;

use Ttpryg\PromotionEngine\Contracts\PromotionUsageRepositoryInterface;
use Ttpryg\PromotionEngine\Entities\PromotionUsage;

class MemoryPromotionUsageRepository implements PromotionUsageRepositoryInterface
{
    /** @var array<string, PromotionUsage> */
    private array $usages = [];

    public function recordUsage(PromotionUsage $promotionUsage): void
    {
        $this->usages[$promotionUsage->id] = $promotionUsage;
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

<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Repositories;

use DateTimeImmutable;
use Ttpryg\PromotionEngine\Contracts\PromotionRepositoryInterface;
use Ttpryg\PromotionEngine\Entities\Promotion;

class MemoryPromotionRepository implements PromotionRepositoryInterface
{
    /** @var array<string, Promotion> */
    private array $promotions = [];

    public function save(Promotion $promotion): void
    {
        $this->promotions[$promotion->id] = $promotion;
    }

    public function incrementUsageCount(string $id, ?DateTimeImmutable $now = null): bool
    {
        $promotion = $this->promotions[$id] ?? null;

        if (! $promotion instanceof Promotion || $promotion->hasReachedGlobalUsageLimit()) {
            return false;
        }

        $promotion->incrementUsage();

        return true;
    }

    public function findById(string $id): ?Promotion
    {
        return $this->promotions[$id] ?? null;
    }

    public function findByCode(string $code, ?string $storeId = null): ?Promotion
    {
        $globalFallback = null;

        foreach ($this->promotions as $promotion) {
            if ($promotion->code === null || strcasecmp($promotion->code, $code) !== 0) {
                continue;
            }

            if ($storeId === null) {
                return $promotion;
            }

            if ($promotion->storeId === $storeId) {
                return $promotion;
            }

            if ($promotion->storeId === null && $globalFallback === null) {
                $globalFallback = $promotion;
            }
        }

        return $globalFallback;
    }

    public function codeExists(string $code, ?string $storeId = null, ?string $excludeId = null): bool
    {
        foreach ($this->promotions as $promotion) {
            if ($promotion->id === $excludeId) {
                continue;
            }

            if ($promotion->code !== null
                && strcasecmp($promotion->code, $code) === 0
                && $promotion->storeId === $storeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, Promotion>
     */
    public function findActivePromotions(?string $storeId = null): array
    {
        $result = [];
        foreach ($this->promotions as $promotion) {
            if ($promotion->isActive && ! $promotion->isExpired()) {
                if ($storeId === null || $promotion->storeId === null || $promotion->storeId === $storeId) {
                    $result[] = $promotion;
                }
            }
        }

        return $result;
    }

    public function delete(string $id): bool
    {
        if (isset($this->promotions[$id])) {
            unset($this->promotions[$id]);

            return true;
        }

        return false;
    }
}

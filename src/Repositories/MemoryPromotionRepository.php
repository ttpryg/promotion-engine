<?php

namespace Ttpryg\PromotionEngine\Repositories;

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

    public function findById(string $id): ?Promotion
    {
        return $this->promotions[$id] ?? null;
    }

    public function findByCode(string $code, ?string $storeId = null): ?Promotion
    {
        foreach ($this->promotions as $promotion) {
            if ($promotion->code !== null && strcasecmp($promotion->code, $code) === 0) {
                if ($storeId === null || $promotion->storeId === null || $promotion->storeId === $storeId) {
                    return $promotion;
                }
            }
        }

        return null;
    }

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

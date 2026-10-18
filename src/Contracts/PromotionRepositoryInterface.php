<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Contracts;

use DateTimeImmutable;
use Ttpryg\PromotionEngine\Entities\Promotion;

interface PromotionRepositoryInterface
{
    public function save(Promotion $promotion): void;

    /**
     * Atomically increment the promotion usage count, but only while the
     * configured global usage limit (if any) has not been reached.
     *
     * @return bool True when the counter was incremented, false when the
     *              promotion is missing or its usage limit has been reached.
     */
    public function incrementUsageCount(string $id, ?DateTimeImmutable $now = null): bool;

    public function findById(string $id): ?Promotion;

    public function findByCode(string $code, ?string $storeId = null): ?Promotion;

    public function findActivePromotions(?string $storeId = null): array;

    public function delete(string $id): bool;
}

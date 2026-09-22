<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Contracts;

use Ttpryg\PromotionEngine\Entities\Promotion;

interface PromotionRepositoryInterface
{
    public function save(Promotion $promotion): void;

    public function findById(string $id): ?Promotion;

    public function findByCode(string $code, ?string $storeId = null): ?Promotion;

    public function findActivePromotions(?string $storeId = null): array;

    public function delete(string $id): bool;
}

<?php

namespace Ttpryg\PromotionEngine\Entities;

use DateTimeImmutable;
use Ttpryg\PromotionEngine\Enums\PromotionType;

class Promotion
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $code,
        public string $name,
        public ?string $description,
        public PromotionType $type,
        public float $value,
        public ?string $storeId = null,
        public ?string $ownerId = null,
        public float $minSpend = 0.0,
        public ?float $maxDiscount = null,
        public ?int $usageLimit = null,
        public int $usageCount = 0,
        public ?int $userUsageLimit = null,
        public ?DateTimeImmutable $startAt = null,
        public ?DateTimeImmutable $endAt = null,
        public bool $isActive = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable;
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        $now = $now ?? new DateTimeImmutable;
        if ($this->startAt !== null && $now < $this->startAt) {
            return true;
        }
        if ($this->endAt !== null && $now > $this->endAt) {
            return true;
        }

        return false;
    }

    public function hasReachedGlobalUsageLimit(): bool
    {
        if ($this->usageLimit === null) {
            return false;
        }

        return $this->usageCount >= $this->usageLimit;
    }

    public function incrementUsage(): void
    {
        $this->usageCount++;
        $this->updatedAt = new DateTimeImmutable;
    }
}

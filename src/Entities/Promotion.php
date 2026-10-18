<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Entities;

use DateTimeImmutable;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Exceptions\InvalidPromotionException;

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

        $this->validate();
    }

    public function validate(): void
    {
        if ($this->code !== null && trim($this->code) === '') {
            throw InvalidPromotionException::emptyCode();
        }

        if ($this->type === PromotionType::PERCENTAGE) {
            if ($this->value < 0.01 || $this->value > 100.00) {
                throw InvalidPromotionException::percentageOutOfRange($this->value);
            }
        } elseif ($this->value < 0.0) {
            throw InvalidPromotionException::negativeAmount('Discount value', $this->value);
        }

        if ($this->minSpend < 0.0) {
            throw InvalidPromotionException::negativeAmount('Minimum spend', $this->minSpend);
        }

        if ($this->maxDiscount !== null && $this->maxDiscount < 0.0) {
            throw InvalidPromotionException::negativeAmount('Maximum discount', $this->maxDiscount);
        }

        if ($this->startAt instanceof DateTimeImmutable
            && $this->endAt instanceof DateTimeImmutable
            && $this->startAt > $this->endAt) {
            throw InvalidPromotionException::invalidDateRange();
        }
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        $now ??= new DateTimeImmutable;
        if ($this->startAt instanceof \DateTimeImmutable && $now < $this->startAt) {
            return true;
        }

        return $this->endAt instanceof \DateTimeImmutable && $now > $this->endAt;
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

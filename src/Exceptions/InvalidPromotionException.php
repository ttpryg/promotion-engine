<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Exceptions;

use InvalidArgumentException;

class InvalidPromotionException extends InvalidArgumentException implements ExceptionInterface
{
    public static function percentageOutOfRange(float $value): self
    {
        return new self(sprintf('Percentage discount value must be between 0.01 and 100.00, got %.2f.', $value));
    }

    public static function negativeAmount(string $field, float $value): self
    {
        return new self(sprintf('%s must not be negative, got %.2f.', $field, $value));
    }

    public static function invalidDateRange(): self
    {
        return new self('Promotion start date must not be later than its end date.');
    }

    public static function emptyCode(): self
    {
        return new self('Promotion coupon code must not be empty.');
    }

    public static function duplicateCode(string $code, ?string $storeId): self
    {
        return new self(sprintf('Coupon code "%s" already exists for store "%s".', $code, $storeId ?? 'global'));
    }
}

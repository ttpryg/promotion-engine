<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Exceptions;

use RuntimeException;

class PromotionUsageLimitExceededException extends RuntimeException implements ExceptionInterface
{
    public function __construct(
        public readonly string $promotionId,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message !== '' ? $message : sprintf('Promotion usage limit exceeded for promotion "%s".', $promotionId),
            0,
            $previous
        );
    }
}

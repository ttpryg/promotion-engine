<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Exceptions;

use RuntimeException;
use Throwable;

class PromotionNotFoundException extends RuntimeException
{
    public function __construct(
        public readonly string $promotionId,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message !== '' ? $message : sprintf('Promotion "%s" was not found.', $promotionId),
            0,
            $previous
        );
    }
}

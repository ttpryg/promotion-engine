<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Exceptions;

use Throwable;

/**
 * Root marker interface for every exception thrown by this library.
 *
 * Consumers may catch this single interface to handle all promotion-engine
 * specific errors, e.g. `catch (ExceptionInterface $e)`.
 */
interface ExceptionInterface extends Throwable {}

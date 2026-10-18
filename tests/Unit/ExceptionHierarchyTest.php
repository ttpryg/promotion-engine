<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\PromotionEngine\Exceptions\ExceptionInterface;
use Ttpryg\PromotionEngine\Exceptions\InvalidPromotionException;
use Ttpryg\PromotionEngine\Exceptions\PromotionNotFoundException;
use Ttpryg\PromotionEngine\Exceptions\PromotionUsageLimitExceededException;

class ExceptionHierarchyTest extends TestCase
{
    public function test_all_custom_exceptions_implement_the_root_marker_interface(): void
    {
        $this->assertInstanceOf(ExceptionInterface::class, InvalidPromotionException::percentageOutOfRange(200.0));
        $this->assertInstanceOf(ExceptionInterface::class, new PromotionNotFoundException('promo-x'));
        $this->assertInstanceOf(ExceptionInterface::class, new PromotionUsageLimitExceededException('promo-x'));
    }

    public function test_exceptions_are_catchable_via_the_root_marker_interface(): void
    {
        $caught = null;

        try {
            throw new PromotionNotFoundException('promo-x');
        } catch (ExceptionInterface $exception) {
            $caught = $exception;
        }

        $this->assertInstanceOf(PromotionNotFoundException::class, $caught);
    }
}

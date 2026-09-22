<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Events;

use Ttpryg\PromotionEngine\Entities\PromotionUsage;

class PromotionUsedEvent
{
    public function __construct(public readonly PromotionUsage $usage) {}
}

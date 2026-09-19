<?php

namespace Ttpryg\PromotionEngine\Events;

use Ttpryg\PromotionEngine\Entities\PromotionUsage;

class PromotionUsedEvent
{
    public function __construct(public readonly PromotionUsage $usage) {}
}

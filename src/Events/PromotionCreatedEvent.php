<?php

namespace Ttpryg\PromotionEngine\Events;

use Ttpryg\PromotionEngine\Entities\Promotion;

class PromotionCreatedEvent
{
    public function __construct(public readonly Promotion $promotion) {}
}

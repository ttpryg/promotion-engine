<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Events;

use Ttpryg\PromotionEngine\Entities\Promotion;

class PromotionCreatedEvent
{
    public function __construct(public readonly Promotion $promotion) {}
}

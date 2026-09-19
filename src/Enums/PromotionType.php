<?php

namespace Ttpryg\PromotionEngine\Enums;

enum PromotionType: string
{
    case FIXED_AMOUNT = 'fixed_amount';
    case PERCENTAGE = 'percentage';
}

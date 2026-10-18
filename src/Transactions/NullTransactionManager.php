<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Transactions;

use Ttpryg\PromotionEngine\Contracts\TransactionManagerInterface;

class NullTransactionManager implements TransactionManagerInterface
{
    public function transactional(callable $callback): mixed
    {
        return $callback();
    }
}

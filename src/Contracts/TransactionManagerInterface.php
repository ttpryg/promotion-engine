<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Contracts;

interface TransactionManagerInterface
{
    /**
     * Run the given callback inside a single ACID database transaction.
     *
     * The callback's return value is returned on success. If the callback
     * throws, the transaction is rolled back and the exception is rethrown.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function transactional(callable $callback): mixed;
}

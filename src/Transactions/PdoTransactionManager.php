<?php

declare(strict_types=1);

namespace Ttpryg\PromotionEngine\Transactions;

use PDO;
use Throwable;
use Ttpryg\PromotionEngine\Contracts\TransactionManagerInterface;

class PdoTransactionManager implements TransactionManagerInterface
{
    public function __construct(private readonly PDO $pdo) {}

    public function transactional(callable $callback): mixed
    {
        if ($this->pdo->inTransaction()) {
            return $callback();
        }

        $this->pdo->beginTransaction();

        try {
            $result = $callback();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();

            throw $exception;
        }

        $this->pdo->commit();

        return $result;
    }
}

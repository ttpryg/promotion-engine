<?php

namespace Ttpryg\PromotionEngine\Repositories;

use PDO;
use Ttpryg\PromotionEngine\Contracts\PromotionUsageRepositoryInterface;
use Ttpryg\PromotionEngine\Entities\PromotionUsage;

class PdoPromotionUsageRepository implements PromotionUsageRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    public function recordUsage(PromotionUsage $promotionUsage): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO promotion_usages (id, promotion_id, user_id, cart_id, order_id, discount_amount, used_at) VALUES (:id, :promotion_id, :user_id, :cart_id, :order_id, :discount_amount, :used_at)');
        $stmt->execute([
            'id' => $promotionUsage->id,
            'promotion_id' => $promotionUsage->promotionId,
            'user_id' => $promotionUsage->userId,
            'cart_id' => $promotionUsage->cartId,
            'order_id' => $promotionUsage->orderId,
            'discount_amount' => $promotionUsage->discountAmount,
            'used_at' => $promotionUsage->usedAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function countUserUsage(string $promotionId, string $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM promotion_usages WHERE promotion_id = :promotion_id AND user_id = :user_id');
        $stmt->execute([
            'promotion_id' => $promotionId,
            'user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }
}

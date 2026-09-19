<?php

namespace Ttpryg\PromotionEngine\Repositories;

use DateTimeImmutable;
use PDO;
use Ttpryg\PromotionEngine\Contracts\PromotionRepositoryInterface;
use Ttpryg\PromotionEngine\Entities\Promotion;
use Ttpryg\PromotionEngine\Enums\PromotionType;

class PdoPromotionRepository implements PromotionRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    public function save(Promotion $promotion): void
    {
        $existing = $this->findById($promotion->id);

        $sql = $existing
            ? 'UPDATE promotions SET code = :code, name = :name, description = :description, type = :type, value = :value, store_id = :store_id, owner_id = :owner_id, min_spend = :min_spend, max_discount = :max_discount, usage_limit = :usage_limit, usage_count = :usage_count, user_usage_limit = :user_usage_limit, start_at = :start_at, end_at = :end_at, is_active = :is_active, updated_at = :updated_at WHERE id = :id'
            : 'INSERT INTO promotions (id, code, name, description, type, value, store_id, owner_id, min_spend, max_discount, usage_limit, usage_count, user_usage_limit, start_at, end_at, is_active, created_at, updated_at) VALUES (:id, :code, :name, :description, :type, :value, :store_id, :owner_id, :min_spend, :max_discount, :usage_limit, :usage_count, :user_usage_limit, :start_at, :end_at, :is_active, :created_at, :updated_at)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $promotion->id,
            'code' => $promotion->code,
            'name' => $promotion->name,
            'description' => $promotion->description,
            'type' => $promotion->type->value,
            'value' => $promotion->value,
            'store_id' => $promotion->storeId,
            'owner_id' => $promotion->ownerId,
            'min_spend' => $promotion->minSpend,
            'max_discount' => $promotion->maxDiscount,
            'usage_limit' => $promotion->usageLimit,
            'usage_count' => $promotion->usageCount,
            'user_usage_limit' => $promotion->userUsageLimit,
            'start_at' => $promotion->startAt?->format('Y-m-d H:i:s'),
            'end_at' => $promotion->endAt?->format('Y-m-d H:i:s'),
            'is_active' => $promotion->isActive ? 1 : 0,
            'created_at' => $promotion->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $promotion->updatedAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?Promotion
    {
        $stmt = $this->pdo->prepare('SELECT * FROM promotions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToEntity($row) : null;
    }

    public function findByCode(string $code, ?string $storeId = null): ?Promotion
    {
        $sql = 'SELECT * FROM promotions WHERE LOWER(code) = LOWER(:code)';
        $params = ['code' => $code];

        if ($storeId !== null) {
            $sql .= ' AND (store_id IS NULL OR store_id = :store_id)';
            $params['store_id'] = $storeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToEntity($row) : null;
    }

    public function findActivePromotions(?string $storeId = null): array
    {
        $sql = 'SELECT * FROM promotions WHERE is_active = 1';
        $params = [];

        if ($storeId !== null) {
            $sql .= ' AND (store_id IS NULL OR store_id = :store_id)';
            $params['store_id'] = $storeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $entity = $this->mapToEntity($row);
            if (! $entity->isExpired()) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    public function delete(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM promotions WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    private function mapToEntity(array $row): Promotion
    {
        return new Promotion(
            id: (string) $row['id'],
            code: $row['code'] ?? null,
            name: (string) $row['name'],
            description: $row['description'] ?? null,
            type: PromotionType::from($row['type']),
            value: (float) $row['value'],
            storeId: $row['store_id'] ?? null,
            ownerId: $row['owner_id'] ?? null,
            minSpend: (float) ($row['min_spend'] ?? 0.0),
            maxDiscount: isset($row['max_discount']) ? (float) $row['max_discount'] : null,
            usageLimit: isset($row['usage_limit']) ? (int) $row['usage_limit'] : null,
            usageCount: (int) ($row['usage_count'] ?? 0),
            userUsageLimit: isset($row['user_usage_limit']) ? (int) $row['user_usage_limit'] : null,
            startAt: ! empty($row['start_at']) ? new DateTimeImmutable($row['start_at']) : null,
            endAt: ! empty($row['end_at']) ? new DateTimeImmutable($row['end_at']) : null,
            isActive: (bool) $row['is_active'],
            createdAt: ! empty($row['created_at']) ? new DateTimeImmutable($row['created_at']) : null,
            updatedAt: ! empty($row['updated_at']) ? new DateTimeImmutable($row['updated_at']) : null
        );
    }
}

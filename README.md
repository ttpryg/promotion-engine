# PromotionEngine Library

`ttpryg/promotion-engine` is a framework-agnostic standalone PHP 8.1+ library for promotion, coupon code evaluation, automatic storewide discounts, and multi-store promo management.

## 🌟 Key Features

- **Framework Agnostic**: Works out of the box with any PHP 8.1+ application or framework (Vanilla PHP, Slim 4, Laravel, Symfony, CodeIgniter).
- **Coupons & Automatic Promotions**: Support for coupon code evaluations (`evaluateCoupon`) and automatic storewide or global promotion claims (`findBestAutomaticPromotion`).
- **Flexible Discount Types**:
  - `FIXED_AMOUNT`: Fixed currency discount (e.g. Rp 50.000 off).
  - `PERCENTAGE`: Percentage discount (e.g. 20% off) with maximum cap limit (`max_discount`).
- **Multi-Tenant / Multi-Store Scoping**: Filter promotions by `store_id` and `owner_id` for multi-store marketplaces.
- **Usage & Eligibility Limits**:
  - Minimum spend thresholds (`min_spend`).
  - Active date range windows (`start_at`, `end_at`).
  - Total global usage limit (`usage_limit`).
  - Per-user usage limit (`user_usage_limit`).
- **Multiple Storage Drivers**:
  - `PdoPromotionRepository` & `PdoPromotionUsageRepository`: Relational database storage via PDO (MariaDB, MySQL, SQLite).
  - `MemoryPromotionRepository` & `MemoryPromotionUsageRepository`: In-memory storage for unit testing.
- **Domain Events**: Dispatches PSR-14 events (`PromotionCreatedEvent`, `PromotionAppliedEvent`, `PromotionUsedEvent`).

---

## 🗄️ Database Schema

Run the SQL script from `database/schema.sql`:

```sql
CREATE TABLE IF NOT EXISTS promotions (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(50) NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type VARCHAR(30) NOT NULL,
    value DECIMAL(12, 2) NOT NULL,
    store_id VARCHAR(36) NULL,
    owner_id VARCHAR(36) NULL,
    min_spend DECIMAL(12, 2) DEFAULT 0.00,
    max_discount DECIMAL(12, 2) NULL,
    usage_limit INT NULL,
    usage_count INT DEFAULT 0,
    user_usage_limit INT NULL,
    start_at DATETIME NULL,
    end_at DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS promotion_usages (
    id VARCHAR(36) PRIMARY KEY,
    promotion_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    cart_id VARCHAR(36) NULL,
    order_id VARCHAR(36) NULL,
    discount_amount DECIMAL(12, 2) NOT NULL,
    used_at DATETIME NOT NULL,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE
);
```

---

## 🚀 Quick Usage Example

```php
use PDO;
use Ttpryg\PromotionEngine\Enums\PromotionType;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionRepository;
use Ttpryg\PromotionEngine\Repositories\PdoPromotionUsageRepository;
use Ttpryg\PromotionEngine\Services\PromotionService;

// 1. Initialize Database & Repositories
$pdo = new PDO("mysql:host=localhost;dbname=my_db", "root", "secret");
$promotionRepo = new PdoPromotionRepository($pdo);
$usageRepo = new PdoPromotionUsageRepository($pdo);

$service = new PromotionService($promotionRepo, $usageRepo);

// 2. Create a Coupon Promotion
$promotion = $service->createPromotion(
    id: 'promo-001',
    name: 'Diskon Merdeka 17%',
    type: PromotionType::PERCENTAGE,
    value: 17.0,
    code: 'MERDEKA17',
    storeId: 'store-100',
    minSpend: 100000.0,
    maxDiscount: 50000.0,
    userUsageLimit: 1
);

// 3. Evaluate Coupon in Shopping Cart
$result = $service->evaluateCoupon('MERDEKA17', [
    'cart_subtotal' => 200000.0,
    'user_id' => 'user-777',
    'store_id' => 'store-100'
]);

if ($result->isEligible) {
    echo "Potongan Diskon: Rp " . number_format($result->discountAmount, 0, ',', '.');
    // Output: Potongan Diskon: Rp 34.000
} else {
    echo "Kupon Tidak Valid: " . $result->reason;
}

// 4. Record Usage upon Checkout
$service->recordPromotionUsage(
    usageId: 'usage-999',
    promotionId: $promotion->id,
    userId: 'user-777',
    discountAmount: $result->discountAmount,
    orderId: 'order-888'
);
```

---

## 📄 License
MIT License.

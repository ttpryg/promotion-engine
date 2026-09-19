CREATE TABLE IF NOT EXISTS promotions (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(50) NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type VARCHAR(30) NOT NULL, -- fixed_amount, percentage
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

-- 001: Tambahkan indeks performa pada tabel promotions & promotion_usages.
--
-- Tujuan: mempercepat evaluasi kode kupon, lookup store, hitung pemakaian per
-- user, dan lookup berdasarkan order.
-- Jalankan hanya pada database yang dibuat sebelum indeks diimplementasikan.
-- Fresh install via DatabaseMigrator::migrate() sudah mencakup indeks ini di
-- schema.sql dan menandai migrasi ini sebagai baseline secara otomatis.
CREATE INDEX IF NOT EXISTS idx_promotions_code ON promotions(code);
CREATE INDEX IF NOT EXISTS idx_promotions_store_active ON promotions(store_id, is_active);
CREATE INDEX IF NOT EXISTS idx_usages_promo_user ON promotion_usages(promotion_id, user_id);
CREATE INDEX IF NOT EXISTS idx_usages_order_id ON promotion_usages(order_id);

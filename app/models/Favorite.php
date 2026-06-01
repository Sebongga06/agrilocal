<?php
require_once __DIR__ . '/../../config/Database.php';

class Favorite
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                f.fav_id,
                f.fav_product_id,
                f.fav_vendor_id,
                f.fav_dateCreated,
                p.prd_name,
                p.prd_price,
                p.prd_unit,
                p.prd_images,
                pv.vnd_farm_name  AS prd_vendor_name,
                pv.vnd_id         AS prd_vendor_id,
                c.cat_name,
                v.vnd_farm_name   AS fav_vnd_farm_name,
                v.vnd_address     AS fav_vnd_address,
                v.vnd_cover_photo AS fav_vnd_cover_photo
            FROM tbl_favorites f
            LEFT JOIN tbl_product  p  ON p.prd_id  = f.fav_product_id
            LEFT JOIN tbl_vendor   pv ON pv.vnd_id = p.prd_vendor_id
            LEFT JOIN tbl_category c  ON c.cat_id  = p.prd_category_id
            LEFT JOIN tbl_vendor   v  ON v.vnd_id  = f.fav_vendor_id
            WHERE f.fav_user_id = ?
            ORDER BY f.fav_dateCreated DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggle(int $userId, ?int $productId, ?int $vendorId): array
    {
        if ($productId) {
            $stmt = $this->db->prepare("SELECT fav_id FROM tbl_favorites WHERE fav_user_id=? AND fav_product_id=? LIMIT 1");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $this->db->prepare("SELECT fav_id FROM tbl_favorites WHERE fav_user_id=? AND fav_vendor_id=? LIMIT 1");
            $stmt->execute([$userId, $vendorId]);
        }

        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $del = $this->db->prepare("DELETE FROM tbl_favorites WHERE fav_id=?");
            $del->execute([$existing['fav_id']]);
            return ['action' => 'removed'];
        }

        $ins = $this->db->prepare("
            INSERT INTO tbl_favorites (fav_user_id, fav_product_id, fav_vendor_id)
            VALUES (?, ?, ?)
        ");
        $ok = $ins->execute([$userId, $productId, $vendorId]);
        if (!$ok) {
            return ['action' => 'error'];
        }
        return ['action' => 'added'];
    }

    public function remove(int $favId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tbl_favorites WHERE fav_id=? AND fav_user_id=?");
        $stmt->execute([$favId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function isFavorited(int $userId, ?int $productId = null, ?int $vendorId = null): bool
    {
        if ($productId) {
            $stmt = $this->db->prepare("SELECT 1 FROM tbl_favorites WHERE fav_user_id=? AND fav_product_id=? LIMIT 1");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $this->db->prepare("SELECT 1 FROM tbl_favorites WHERE fav_user_id=? AND fav_vendor_id=? LIMIT 1");
            $stmt->execute([$userId, $vendorId]);
        }
        return (bool)$stmt->fetch();
    }
}

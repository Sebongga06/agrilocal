<?php
require_once __DIR__ . '/../../config/Database.php';

class Vendor {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT 
                v.vnd_id,
                v.vnd_farm_name,
                v.vnd_address,
                v.vnd_farm_desc,
                v.vnd_pickup_instructions,
                v.vnd_cover_photo,
                v.vnd_lat,
                v.vnd_lng
            FROM tbl_vendor v
            ORDER BY v.vnd_id ASC
        ");
        $stmt->execute();

        $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($vendor) {
            $vendorId = (int)$vendor['vnd_id'];
            $reviewStats = $this->getVendorReviewStats($vendorId);

            return [
                'id' => $vendorId,
                'name' => $vendor['vnd_farm_name'],
                'image' => !empty($vendor['vnd_cover_photo'])
                    ? $vendor['vnd_cover_photo']
                    : 'https://via.placeholder.com/600x300?text=Vendor',
                'rating' => $reviewStats['rating'],
                'reviews' => $reviewStats['reviews'],
                'address' => $vendor['vnd_address'] ?? '',
                'description' => $vendor['vnd_farm_desc'] ?? '',
                'pickup_instructions' => $vendor['vnd_pickup_instructions'] ?? '',
                'slug' => $vendorId,
                'category' => $this->getVendorPrimaryCategory($vendorId),
                'distance' => 0,
                'lat' => !empty($vendor['vnd_lat']) ? (float)$vendor['vnd_lat'] : 10.6755,
                'lng' => !empty($vendor['vnd_lng']) ? (float)$vendor['vnd_lng'] : 122.9588,
                'products' => $this->getVendorProductNames($vendorId),
            ];
        }, $vendors);
    }

    public function getProfileById(int $vendorId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                v.vnd_id,
                v.vnd_user_id,
                v.vnd_farm_name,
                v.vnd_owner_name,
                v.vnd_address,
                v.vnd_farm_desc,
                v.vnd_pickup_instructions,
                v.vnd_cover_photo,
                v.vnd_profile_pic,
                v.vnd_lat,
                v.vnd_lng,
                COALESCE(AVG(r.rev_rating), 0) AS vnd_rating_avg
            FROM tbl_vendor v
            LEFT JOIN tbl_review r ON r.rev_vendor_id = v.vnd_id
            WHERE v.vnd_id = ?
            GROUP BY
                v.vnd_id,
                v.vnd_user_id,
                v.vnd_farm_name,
                v.vnd_owner_name,
                v.vnd_address,
                v.vnd_farm_desc,
                v.vnd_pickup_instructions,
                v.vnd_cover_photo,
                v.vnd_profile_pic,
                v.vnd_lat,
                v.vnd_lng
            LIMIT 1
        ");
        $stmt->execute([$vendorId]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$vendor) return null;

        return $vendor;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                v.vnd_id,
                v.vnd_farm_name,
                v.vnd_address,
                v.vnd_farm_desc,
                v.vnd_pickup_instructions,
                v.vnd_cover_photo,
                v.vnd_lat,
                v.vnd_lng
            FROM tbl_vendor v
            WHERE v.vnd_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$id]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vendor) {
            return null;
        }

        $vendorId = (int)$vendor['vnd_id'];
        $reviewStats = $this->getVendorReviewStats($vendorId);

        return [
            'id' => $vendorId,
            'name' => $vendor['vnd_farm_name'],
            'image' => !empty($vendor['vnd_cover_photo'])
                ? $vendor['vnd_cover_photo']
                : 'https://via.placeholder.com/600x300?text=Vendor',
            'rating' => $reviewStats['rating'],
            'reviews' => $reviewStats['reviews'],
            'address' => $vendor['vnd_address'] ?? '',
            'description' => $vendor['vnd_farm_desc'] ?? '',
            'pickup_instructions' => $vendor['vnd_pickup_instructions'] ?? '',
            'delivery_fee' => 0,
            'slug' => $vendorId,
            'category' => $this->getVendorPrimaryCategory($vendorId),
            'distance' => 0,
            'lat' => !empty($vendor['vnd_lat']) ? (float)$vendor['vnd_lat'] : 10.6755,
            'lng' => !empty($vendor['vnd_lng']) ? (float)$vendor['vnd_lng'] : 122.9588,
            'products' => $this->getVendorProductNames($vendorId),
        ];
    }

    public function getBySlug($slug) {
        return $this->getById((int)$slug);
    }

    public function getVendorIdByUserId(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT vnd_id
            FROM tbl_vendor
            WHERE vnd_user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($row['vnd_id'] ?? 0);
    }

    public function getProducts($vendorId) {
        $stmt = $this->db->prepare("
            SELECT 
                prd_id,
                prd_name,
                prd_description,
                prd_price,
                prd_unit,
                prd_stock_quantity,
                prd_images
            FROM tbl_product
            WHERE prd_vendor_id = ?
            ORDER BY prd_name ASC
        ");
        $stmt->execute([(int)$vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasReviewed(int $userId, int $orderId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tbl_review
            WHERE rev_user_id = ? AND rev_order_id = ?
        ");
        $stmt->execute([$userId, $orderId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function addReview(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_review (rev_user_id, rev_vendor_id, rev_order_id, rev_rating, rev_title, rev_comment)
            VALUES (:user_id, :vendor_id, :order_id, :rating, :title, :comment)
        ");
        return $stmt->execute([
            ':user_id'   => $data['user_id'],
            ':vendor_id' => $data['vendor_id'],
            ':order_id'  => $data['order_id'],
            ':rating'    => $data['rating'],
            ':title'     => $data['title'],
            ':comment'   => $data['comment'],
        ]);
    }

    public function getReviews($vendorId) {
        $stmt = $this->db->prepare("
            SELECT 
                r.*,
                CONCAT(u.user_fname, ' ', u.user_lname) AS rev_name
            FROM tbl_review r
            LEFT JOIN tbl_user u ON r.rev_user_id = u.user_id
            WHERE r.rev_vendor_id = ?
            ORDER BY r.rev_dateCreated DESC
        ");
        $stmt->execute([(int)$vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getVendorProductNames($vendorId) {
        $stmt = $this->db->prepare("
            SELECT prd_name
            FROM tbl_product
            WHERE prd_vendor_id = ?
            ORDER BY prd_name ASC
            LIMIT 4
        ");
        $stmt->execute([(int)$vendorId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($rows)) {
            return 'No products listed yet';
        }

        return implode(', ', $rows);
    }

    private function getVendorPrimaryCategory(int $vendorId): string
    {
        $stmt = $this->db->prepare("
            SELECT c.cat_name, COUNT(*) AS total
            FROM tbl_product p
            LEFT JOIN tbl_category c ON p.prd_category_id = c.cat_id
            WHERE p.prd_vendor_id = ?
            GROUP BY c.cat_name
            ORDER BY total DESC, c.cat_name ASC
            LIMIT 1
        ");
        $stmt->execute([$vendorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['cat_name'])) {
            return 'vegetables';
        }

        $name = strtolower(trim($row['cat_name']));

        // Map DB category names to chip filter values
        $map = [
            'vegetable'  => 'vegetables',
            'vegetables' => 'vegetables',
            'fruit'      => 'fruits',
            'fruits'     => 'fruits',
            'dairy'      => 'dairy',
            'herb'       => 'herbs',
            'herbs'      => 'herbs',
        ];

        foreach ($map as $key => $chip) {
            if (str_contains($name, $key)) {
                return $chip;
            }
        }

        return 'vegetables';
    }

    private function getVendorReviewStats(int $vendorId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS review_count,
                AVG(rev_rating) AS avg_rating
            FROM tbl_review
            WHERE rev_vendor_id = ?
        ");
        $stmt->execute([$vendorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'rating' => isset($row['avg_rating']) && $row['avg_rating'] !== null
                ? round((float)$row['avg_rating'], 1)
                : 0,
            'reviews' => (int)($row['review_count'] ?? 0),
        ];
    }
}
<?php
require_once __DIR__ . '/../../config/Database.php';

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    // -------------------------------------------------------
    // READ – all products for buyer pages with filters
    // -------------------------------------------------------
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT
                p.*,
                v.vnd_farm_name,
                v.vnd_address,
                v.vnd_pickup_instructions,
                c.cat_name
            FROM tbl_product p
            JOIN tbl_vendor v ON p.prd_vendor_id = v.vnd_id
            LEFT JOIN tbl_category c ON p.prd_category_id = c.cat_id
            WHERE p.prd_is_available = 1
        ";

        $params = [];

        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $categories = array_values(array_filter($filters['categories']));
            if (!empty($categories)) {
                $placeholders = [];
                foreach ($categories as $index => $category) {
                    $key = ':category_' . $index;
                    $placeholders[] = $key;
                    $params[$key] = $category;
                }
                $sql .= " AND c.cat_name IN (" . implode(', ', $placeholders) . ")";
            }
        }

        if (!empty($filters['in_stock'])) {
            $sql .= " AND p.prd_stock_quantity > 0";
        }

        if (!empty($filters['in_season'])) {
            $sql .= " AND p.prd_is_in_season = 1";
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $sql .= " AND p.prd_price <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.prd_name LIKE :search1 OR p.prd_description LIKE :search2 OR v.vnd_farm_name LIKE :search3)";
            $params[':search1'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
            $params[':search3'] = '%' . $filters['search'] . '%';
        }

        $sort = $filters['sort'] ?? 'relevance';
        switch ($sort) {
            case 'price_low':
                $sql .= " ORDER BY p.prd_price ASC, p.prd_id DESC";
                break;
            case 'price_high':
                $sql .= " ORDER BY p.prd_price DESC, p.prd_id DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.prd_dateCreated DESC, p.prd_id DESC";
                break;
            default:
                $sql .= " ORDER BY p.prd_is_in_season DESC, p.prd_dateCreated DESC, p.prd_id DESC";
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $product['image'] = $this->extractPrimaryImage(
                $product['prd_images'] ?? null,
                $product['prd_name'] ?? '',
                $product['cat_name'] ?? ''
            );
        }

        return $products;
    }

    // -------------------------------------------------------
    // READ – all products for a vendor (with optional filters)
    // -------------------------------------------------------
    public function getByVendor(int $vendorId, string $search = '', int $categoryId = 0): array
    {
        $sql = "
            SELECT
                p.*,
                c.cat_name
            FROM tbl_product p
            LEFT JOIN tbl_category c ON p.prd_category_id = c.cat_id
            WHERE p.prd_vendor_id = :vendor_id
        ";

        $params = [
            ':vendor_id' => $vendorId,
        ];

        if ($search !== '') {
            $sql .= " AND p.prd_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if ($categoryId > 0) {
            $sql .= " AND p.prd_category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        $sql .= " ORDER BY p.prd_dateCreated DESC, p.prd_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $product['image'] = $this->extractPrimaryImage(
                $product['prd_images'] ?? null,
                $product['prd_name'] ?? '',
                $product['cat_name'] ?? ''
            );
        }

        return $products;
    }

    // -------------------------------------------------------
    // READ – single product
    // -------------------------------------------------------
    public function getById(int $productId, ?int $vendorId = null): ?array
    {
        $sql = "
            SELECT
                p.*,
                v.vnd_id,
                v.vnd_farm_name,
                v.vnd_address,
                v.vnd_pickup_instructions,
                c.cat_name
            FROM tbl_product p
            JOIN tbl_vendor v ON p.prd_vendor_id = v.vnd_id
            LEFT JOIN tbl_category c ON p.prd_category_id = c.cat_id
            WHERE p.prd_id = :product_id
        ";

        $params = [
            ':product_id' => $productId,
        ];

        if ($vendorId !== null) {
            $sql .= " AND p.prd_vendor_id = :vendor_id";
            $params[':vendor_id'] = $vendorId;
        } else {
            $sql .= " AND p.prd_is_available = 1";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return null;
        }

        $product['image'] = $this->extractPrimaryImage(
            $product['prd_images'] ?? null,
            $product['prd_name'] ?? '',
            $product['cat_name'] ?? ''
        );

        return $product;
    }

    // -------------------------------------------------------
    // READ – summary stats for the inventory header cards
    // -------------------------------------------------------
    public function getStats(int $vendorId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN prd_stock_quantity <= 10 THEN 1 ELSE 0 END) AS low_stock,
                SUM(CASE WHEN prd_is_in_season = 1 AND prd_is_available = 1 THEN 1 ELSE 0 END) AS in_season,
                SUM(CASE WHEN prd_is_available = 0 THEN 1 ELSE 0 END) AS drafts
            FROM tbl_product
            WHERE prd_vendor_id = :vendor_id
        ");
        $stmt->execute([
            ':vendor_id' => $vendorId,
        ]);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => (int)($stats['total'] ?? 0),
            'low_stock' => (int)($stats['low_stock'] ?? 0),
            'in_season' => (int)($stats['in_season'] ?? 0),
            'drafts' => (int)($stats['drafts'] ?? 0),
        ];
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------
    public function create(array $data): bool|int
    {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_product (
                prd_vendor_id,
                prd_category_id,
                prd_name,
                prd_description,
                prd_price,
                prd_unit,
                prd_stock_quantity,
                prd_is_available,
                prd_is_in_season,
                prd_images
            ) VALUES (
                :vendor_id,
                :category_id,
                :name,
                :description,
                :price,
                :unit,
                :stock_quantity,
                :is_available,
                :is_in_season,
                :images
            )
        ");

        $ok = $stmt->execute([
            ':vendor_id' => $data['vendor_id'],
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':unit' => $data['unit'],
            ':stock_quantity' => $data['stock_quantity'],
            ':is_available' => $data['is_available'],
            ':is_in_season' => $data['is_in_season'],
            ':images' => $data['images'],
        ]);

        if (!$ok) {
            return false;
        }

        return (int)$this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------
    public function update(int $productId, int $vendorId, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_product
            SET
                prd_category_id = :category_id,
                prd_name = :name,
                prd_description = :description,
                prd_price = :price,
                prd_unit = :unit,
                prd_stock_quantity = :stock_quantity,
                prd_is_available = :is_available,
                prd_is_in_season = :is_in_season,
                prd_images = :images,
                prd_dateUpdated = NOW()
            WHERE prd_id = :product_id
              AND prd_vendor_id = :vendor_id
        ");

        return $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':unit' => $data['unit'],
            ':stock_quantity' => $data['stock_quantity'],
            ':is_available' => $data['is_available'],
            ':is_in_season' => $data['is_in_season'],
            ':images' => $data['images'],
            ':product_id' => $productId,
            ':vendor_id' => $vendorId,
        ]);
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------
    public function delete(int $productId, int $vendorId): bool
    {
        $product = $this->getById($productId, $vendorId);
        if (!$product) {
            return false;
        }

        // Remove image file if present
        $existingImages = json_decode($product['prd_images'] ?? '[]', true);
        if (!empty($existingImages[0])) {
            $this->deleteImage($existingImages[0]);
        }

        // Remove all referencing rows before deleting the product
        // (foreign key constraints: cart_item, order_item, favorites, review)
        $refs = [
            "DELETE FROM tbl_cart_item  WHERE cit_product_id = :id",
            "DELETE FROM tbl_order_item WHERE oit_product_id = :id",
            "DELETE FROM tbl_favorites  WHERE fav_product_id = :id",
            "UPDATE tbl_review SET rev_product_id = NULL WHERE rev_product_id = :id",
        ];

        foreach ($refs as $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $productId]);
        }

        $stmt = $this->db->prepare("
            DELETE FROM tbl_product
            WHERE prd_id = :product_id
              AND prd_vendor_id = :vendor_id
        ");

        $stmt->execute([
            ':product_id' => $productId,
            ':vendor_id'  => $vendorId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // All categories (for dropdowns)
    // -------------------------------------------------------
    public function getCategories(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM tbl_category
            ORDER BY cat_name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteImage(string $imagePath): void
    {
        if ($imagePath === '') {
            return;
        }

        $fullPath = __DIR__ . '/../../public/' . ltrim($imagePath, '/');
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    public function uploadImage(array $file): array
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;
        $uploadDir = __DIR__ . '/../../public/assets/img/products/';

        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Please select a valid image to upload.', 'path' => ''];
        }

        if ($file['size'] > $maxSize) {
            return ['error' => 'Image must be under 5 MB.', 'path' => ''];
        }

        $tmpName = $file['tmp_name'];
        $mime = mime_content_type($tmpName) ?: ($file['type'] ?? '');
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

        if (!in_array($mime, $allowedMime, true) || !in_array($ext, $allowedExt, true)) {
            return ['error' => 'Only JPG, PNG, and WebP images are allowed.', 'path' => ''];
        }

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['error' => 'Failed to prepare the upload folder.', 'path' => ''];
        }

        $filename = uniqid('prd_', true) . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            return ['error' => 'Failed to save the uploaded image.', 'path' => ''];
        }

        return ['error' => '', 'path' => 'assets/img/products/' . $filename];
    }

    private function extractPrimaryImage(?string $imagesJson, string $productName = '', string $categoryName = ''): string
    {
        $images = json_decode($imagesJson ?? '[]', true);

        if (is_array($images) && !empty($images[0])) {
            return $images[0];
        }

        $name = strtolower(trim($productName));
        $category = strtolower(trim($categoryName));

        $nameBased = [
            'tomatoes' => 'https://images.pexels.com/photos/533280/pexels-photo-533280.jpeg',
            'tomato' => 'https://images.pexels.com/photos/533280/pexels-photo-533280.jpeg',
            'carrots' => 'https://images.pexels.com/photos/143133/pexels-photo-143133.jpeg',
            'carrot' => 'https://images.pexels.com/photos/143133/pexels-photo-143133.jpeg',
            'banana' => 'https://images.pexels.com/photos/1093038/pexels-photo-1093038.jpeg',
            'bananas' => 'https://images.pexels.com/photos/1093038/pexels-photo-1093038.jpeg',
            'spinach' => 'https://images.pexels.com/photos/2329440/pexels-photo-2329440.jpeg',
            'fresh spinach' => 'https://images.pexels.com/photos/2329440/pexels-photo-2329440.jpeg',
            'mangoes' => 'https://images.pexels.com/photos/918643/pexels-photo-918643.jpeg',
            'mango' => 'https://images.pexels.com/photos/918643/pexels-photo-918643.jpeg',
            'watermelons' => 'https://images.pexels.com/photos/1313267/pexels-photo-1313267.jpeg',
            'watermelon' => 'https://images.pexels.com/photos/1313267/pexels-photo-1313267.jpeg',
            'pineapples' => 'https://images.pexels.com/photos/947879/pexels-photo-947879.jpeg',
            'pineapple' => 'https://images.pexels.com/photos/947879/pexels-photo-947879.jpeg',
            'milk' => 'https://images.pexels.com/photos/248412/pexels-photo-248412.jpeg',
            'cheese' => 'https://images.pexels.com/photos/773253/pexels-photo-773253.jpeg',
            'yogurt' => 'https://images.pexels.com/photos/704569/pexels-photo-704569.jpeg',
        ];

        if (isset($nameBased[$name])) {
            return $nameBased[$name];
        }

        $categoryBased = [
            'vegetables' => 'https://images.pexels.com/photos/1458694/pexels-photo-1458694.jpeg',
            'fruits' => 'https://images.pexels.com/photos/1132047/pexels-photo-1132047.jpeg',
            'dairy' => 'https://images.pexels.com/photos/248412/pexels-photo-248412.jpeg',
            'herbs' => 'https://images.pexels.com/photos/4198019/pexels-photo-4198019.jpeg',
        ];

        if (isset($categoryBased[$category])) {
            return $categoryBased[$category];
        }

        return 'https://via.placeholder.com/600x400?text=No+Image';
    }
}
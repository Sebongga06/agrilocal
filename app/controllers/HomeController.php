<?php
require_once __DIR__ . '/../models/Vendor.php';

class HomeController extends Controller
{
    public function index()
    {
        $vendorModel = new Vendor();
        $vendors = $vendorModel->getAll();

        $this->view('buyer/home', [
            'vendors' => $vendors
        ]);
    }

    /**
     * Public JSON list for the global nav search (same shape as home.php → vendorsData).
     */
    public function vendorsJson()
    {
        header('Content-Type: application/json; charset=utf-8');

        $vendorModel = new Vendor();
        $vendors = $vendorModel->getAll();

        $out = array_map(static function (array $vendor): array {
            $vendorId = (int)($vendor['id'] ?? 0);

            return [
                'id'          => $vendorId,
                'name'        => $vendor['name'] ?? 'Vendor',
                'category'    => strtolower($vendor['category'] ?? 'vegetables'),
                'rating'      => (float)($vendor['rating'] ?? 0),
                'reviews'     => (int)($vendor['reviews'] ?? 0),
                'distance'    => (float)($vendor['distance'] ?? 0),
                'lat'         => (float)($vendor['lat'] ?? 10.6755),
                'lng'         => (float)($vendor['lng'] ?? 122.9588),
                'image'       => !empty($vendor['image']) ? $vendor['image'] : 'https://via.placeholder.com/600x300?text=Vendor',
                'products'    => $vendor['products'] ?? 'No products listed yet',
                'description' => $vendor['description'] ?? '',
                'address'     => $vendor['address'] ?? '',
                'storeUrl'    => 'index.php?url=vendors/store/' . $vendorId,
            ];
        }, $vendors);

        echo json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
<?php
require_once __DIR__ . '/../models/Product.php';

class ProductsController extends Controller
{
    public function index()
    {
        $productModel = new Product();

        $search  = trim($_GET['search'] ?? '');
        $filters = [
            'categories' => $_GET['category'] ?? [],
            'max_price'  => $_GET['max_price'] ?? 5000,
            'in_stock'   => isset($_GET['in_stock']) ? 1 : 0,
            'in_season'  => isset($_GET['in_season']) ? 1 : 0,
            'sort'       => $_GET['sort'] ?? 'relevance',
            'search'     => $search,
        ];

        if (!is_array($filters['categories'])) {
            $filters['categories'] = [$filters['categories']];
        }

        $products = $productModel->getAll($filters);

        $this->view('buyer/products', [
            'products' => $products,
            'filters'  => $filters,
            'search'   => $search,
        ]);
    }

    public function detail($id = null)
    {
        $productId = (int)($id ?? 0);

        if ($productId <= 0) {
            die('Product ID is required.');
        }

        $productModel = new Product();
        $product = $productModel->getById($productId);

        if (!$product) {
            die('Product not found.');
        }

        $this->view('buyer/product_detail', ['product' => $product]);
    }
}
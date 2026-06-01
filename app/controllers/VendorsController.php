<?php
require_once __DIR__ . '/../models/Vendor.php';

class VendorsController extends Controller
{
    public function index()
    {
        $vendorModel = new Vendor();
        $vendors = $vendorModel->getAll();

        $this->view('buyer/vendors', [
            'vendors' => $vendors
        ]);
    }

    public function store($id = null)
    {
        $vendorId = (int)($id ?? 0);

        if ($vendorId <= 0) {
            die('Vendor not found.');
        }

        $vendorModel = new Vendor();
        $vendor = $vendorModel->getById($vendorId);

        if (!$vendor) {
            die('Vendor not found.');
        }

        $products = $vendorModel->getProducts($vendorId);
        $reviews = $vendorModel->getReviews($vendorId);

        $this->view('buyer/vendorStore', [
            'vendor' => $vendor,
            'products' => $products,
            'reviews' => $reviews
        ]);
    }
}
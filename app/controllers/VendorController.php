<?php
require_once __DIR__ . '/../models/Vendor.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';

class VendorController extends Controller
{
    private Product $productModel;
    private Vendor $vendorModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->vendorModel = new Vendor();
    }

    // ------------------------------------------------------------------
    // Helper – get the logged-in vendor's vnd_id using the logged-in user
    // ------------------------------------------------------------------
    private function getVendorId(): int
    {
        $userId = (int)($_SESSION['user']['user_id'] ?? 0);

        if ($userId <= 0) {
            header('Location: index.php?url=farmer');
            exit;
        }

        // If session was overwritten by buyer tab, restore vendor role from DB
        if (($_SESSION['user']['role'] ?? '') !== 'vendor') {
            // Check if this user actually has a vendor profile
            $vendorId = $this->vendorModel->getVendorIdByUserId($userId);
            if ($vendorId <= 0) {
                header('Location: index.php?url=farmer');
                exit;
            }
            // Restore vendor session data
            $_SESSION['user']['role']       = 'vendor';
            $_SESSION['user']['has_vendor'] = true;
            $_SESSION['vendor_id']          = $vendorId;
            return $vendorId;
        }

        // Use cached vendor_id from session if available
        $vendorId = (int)($_SESSION['vendor_id'] ?? 0);
        if ($vendorId > 0) return $vendorId;

        $vendorId = $this->vendorModel->getVendorIdByUserId($userId);

        if ($vendorId <= 0) {
            header('Location: index.php?url=farmer');
            exit;
        }

        $_SESSION['vendor_id'] = $vendorId;
        return $vendorId;
    }

    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------
    public function dashboard()
    {
        $vendorId   = $this->getVendorId();
        $orderModel = new Order();

        $counts     = $orderModel->getTabCounts($vendorId);
        $stats      = $this->productModel->getStats($vendorId);
        $todaySales = $orderModel->getTodaySales($vendorId);
        $recent     = $orderModel->getRecent($vendorId, 5);

        $this->view('vendor/dashboard', compact('counts', 'stats', 'todaySales', 'recent'));
    }

    // ------------------------------------------------------------------
    // READ – Inventory list
    // ------------------------------------------------------------------
    public function inventory()
    {
        $vendorId   = $this->getVendorId();
        $search     = trim($_GET['search'] ?? '');
        $categoryId = (int)($_GET['category'] ?? 0);

        $products   = $this->productModel->getByVendor($vendorId, $search, $categoryId);
        $stats      = $this->productModel->getStats($vendorId);
        $categories = $this->productModel->getCategories();

        $this->view('vendor/inventory', compact('products', 'stats', 'categories', 'search', 'categoryId'));
    }

    // ------------------------------------------------------------------
    // CREATE – show form / handle POST
    // ------------------------------------------------------------------
    public function addProduct()
    {
        $vendorId   = $this->getVendorId();
        $categories = $this->productModel->getCategories();
        $errors     = [];
        $success    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['name'] ?? '');
            $categoryId  = (int)($_POST['category_id'] ?? 0);
            $price       = (float)($_POST['price'] ?? 0);
            $unit        = trim($_POST['unit'] ?? '');
            $stock       = (float)($_POST['stock_quantity'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $isInSeason  = isset($_POST['is_in_season']) ? 1 : 0;
            $isAvailable = isset($_POST['is_available']) ? 1 : 0;

            if ($name === '')      $errors[] = 'Product name is required.';
            if ($categoryId === 0) $errors[] = 'Please select a category.';
            if ($price <= 0)       $errors[] = 'Price must be greater than zero.';
            if ($unit === '')      $errors[] = 'Unit is required (e.g. kg, pc).';
            if ($stock < 0)        $errors[] = 'Stock quantity cannot be negative.';

            $imagesJson = null;
            $removeImage = isset($_POST['remove_image']);

            if (!$removeImage && !empty($_FILES['image']['name'])) {
                $uploadResult = $this->productModel->uploadImage($_FILES['image']);
                if ($uploadResult['error']) {
                    $errors[] = $uploadResult['error'];
                } else {
                    $imagesJson = json_encode([$uploadResult['path']]);
                }
            }

            if (empty($errors)) {
                $newId = $this->productModel->create([
                    'vendor_id'      => $vendorId,
                    'category_id'    => $categoryId,
                    'name'           => $name,
                    'description'    => $description,
                    'price'          => $price,
                    'unit'           => $unit,
                    'stock_quantity' => $stock,
                    'is_available'   => $isAvailable,
                    'is_in_season'   => $isInSeason,
                    'images'         => $imagesJson,
                ]);

                if ($newId) {
                    header('Location: index.php?url=vendor/inventory&added=1');
                    exit;
                } else {
                    $errors[] = 'Failed to save product. Please try again.';
                }
            }
        }

        $this->view('vendor/add-product', compact('categories', 'errors', 'success'));
    }

    // ------------------------------------------------------------------
    // UPDATE – show form / handle POST
    // ------------------------------------------------------------------
    public function editProduct()
    {
        $vendorId  = $this->getVendorId();
        $productId = (int)($_GET['id'] ?? 0);

        if ($productId === 0) {
            header('Location: index.php?url=vendor/inventory');
            exit;
        }

        $product = $this->productModel->getById($productId, $vendorId);
        if (!$product) {
            header('Location: index.php?url=vendor/inventory');
            exit;
        }

        $categories = $this->productModel->getCategories();
        $errors     = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['name'] ?? '');
            $categoryId  = (int)($_POST['category_id'] ?? 0);
            $price       = (float)($_POST['price'] ?? 0);
            $unit        = trim($_POST['unit'] ?? '');
            $stock       = (float)($_POST['stock_quantity'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $isInSeason  = isset($_POST['is_in_season']) ? 1 : 0;
            $isAvailable = isset($_POST['is_available']) ? 1 : 0;

            if ($name === '')      $errors[] = 'Product name is required.';
            if ($categoryId === 0) $errors[] = 'Please select a category.';
            if ($price <= 0)       $errors[] = 'Price must be greater than zero.';
            if ($unit === '')      $errors[] = 'Unit is required (e.g. kg, pc).';
            if ($stock < 0)        $errors[] = 'Stock quantity cannot be negative.';

            $imagesJson  = $product['prd_images'];
            $removeImage = isset($_POST['remove_image']);

            if ($removeImage) {
                $existing = json_decode($product['prd_images'] ?? '[]', true);
                if (!empty($existing[0])) {
                    $this->productModel->deleteImage($existing[0]);
                }
                $imagesJson = null;
            } elseif (!empty($_FILES['image']['name'])) {
                $uploadResult = $this->productModel->uploadImage($_FILES['image']);
                if ($uploadResult['error']) {
                    $errors[] = $uploadResult['error'];
                } else {
                    $existing = json_decode($product['prd_images'] ?? '[]', true);
                    if (!empty($existing[0])) {
                        $this->productModel->deleteImage($existing[0]);
                    }
                    $imagesJson = json_encode([$uploadResult['path']]);
                }
            }

            if (empty($errors)) {
                $ok = $this->productModel->update($productId, $vendorId, [
                    'category_id'    => $categoryId,
                    'name'           => $name,
                    'description'    => $description,
                    'price'          => $price,
                    'unit'           => $unit,
                    'stock_quantity' => $stock,
                    'is_available'   => $isAvailable,
                    'is_in_season'   => $isInSeason,
                    'images'         => $imagesJson,
                ]);

                if ($ok) {
                    header('Location: index.php?url=vendor/inventory&updated=1');
                    exit;
                } else {
                    $errors[] = 'Failed to update product. Please try again.';
                }
            }

            $product = array_merge($product, [
                'prd_name'           => $name,
                'prd_category_id'    => $categoryId,
                'prd_price'          => $price,
                'prd_unit'           => $unit,
                'prd_stock_quantity' => $stock,
                'prd_description'    => $description,
                'prd_is_in_season'   => $isInSeason,
                'prd_is_available'   => $isAvailable,
            ]);
        }

        $this->view('vendor/edit-product', compact('product', 'categories', 'errors'));
    }

    // ------------------------------------------------------------------
    // DELETE – POST only
    // ------------------------------------------------------------------
    public function deleteProduct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=vendor/inventory');
            exit;
        }

        $vendorId  = $this->getVendorId();
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            $this->productModel->delete($productId, $vendorId);
        }

        header('Location: index.php?url=vendor/inventory&deleted=1');
        exit;
    }

    // ------------------------------------------------------------------
    // Orders – list with tab filter
    // ------------------------------------------------------------------
    public function orders()
    {
        $vendorId  = $this->getVendorId();
        $tab       = $_GET['tab'] ?? 'new';
        $validTabs = ['new', 'ready', 'completed', 'cancelled'];
        if (!in_array($tab, $validTabs)) $tab = 'new';

        $orderModel   = new Order();
        $orders       = $orderModel->getByVendor($vendorId, $tab);
        $counts       = $orderModel->getTabCounts($vendorId);
        $urgentCount  = $orderModel->getUrgentCount($vendorId);

        $this->view('vendor/orders', compact('orders', 'tab', 'counts', 'urgentCount'));
    }

    // ------------------------------------------------------------------
    // READ + UPDATE status – Order Details page
    // ------------------------------------------------------------------
    public function orderDetails($orderId = null)
    {
        $vendorId = $this->getVendorId();
        $orderId  = (int)($orderId ?? $_GET['id'] ?? 0);

        if ($orderId === 0) {
            header('Location: index.php?url=vendor/orders');
            exit;
        }

        $orderModel = new Order();
        $order      = $orderModel->getById($orderId, $vendorId);

        if (!$order) {
            header('Location: index.php?url=vendor/orders');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action  = $_POST['action'] ?? '';
            $fromTab = in_array($_POST['from_tab'] ?? '', ['new','ready','completed','cancelled'])
                       ? $_POST['from_tab']
                       : 'new';

            if ($action === 'mark_ready') {
                $orderModel->updateStatus($orderId, $vendorId, 'ready');
                header('Location: index.php?url=vendor/orders&tab=ready&updated=1');
            } elseif ($action === 'cancel') {
                $orderModel->updateStatus($orderId, $vendorId, 'cancelled');
                header('Location: index.php?url=vendor/orders&tab=cancelled&updated=1');
            } elseif ($action === 'verify_payment') {
                $orderModel->updatePaymentStatus($orderId, $vendorId, 'paid');
                header('Location: index.php?url=vendor/orderDetails/' . $orderId . '&from=' . $fromTab . '&updated=1');
            } elseif ($action === 'reject_payment') {
                $orderModel->updatePaymentStatus($orderId, $vendorId, 'rejected');
                header('Location: index.php?url=vendor/orderDetails/' . $orderId . '&from=' . $fromTab . '&updated=1');
            } else {
                header('Location: index.php?url=vendor/orderDetails/' . $orderId . '&from=' . $fromTab);
            }
            exit;
        }

        $items        = $orderModel->getOrderItems($orderId);
        $urgentCount  = $orderModel->getUrgentCount($vendorId);
        $this->view('vendor/order-details', compact('order', 'items', 'urgentCount'));
    }

    // ------------------------------------------------------------------
    // UPDATE – Edit order details (BUYER ONLY — vendors cannot edit orders)
    // ------------------------------------------------------------------
    public function editOrder($orderId = null)
    {
        // Vendors are not permitted to edit buyer orders
        header('Location: index.php?url=vendor/orders');
        exit;
    }

    // ------------------------------------------------------------------
    // DELETE – Permanently remove a cancelled order
    // ------------------------------------------------------------------
    public function deleteOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=vendor/orders&tab=cancelled');
            exit;
        }

        $vendorId = $this->getVendorId();
        $orderId  = (int)($_POST['order_id'] ?? 0);

        if ($orderId > 0) {
            $orderModel = new Order();
            $orderModel->delete($orderId, $vendorId);
        }

        header('Location: index.php?url=vendor/orders&tab=cancelled&deleted=1');
        exit;
    }

    // ------------------------------------------------------------------
    // Profile
    // ------------------------------------------------------------------
    public function profile()
    {
        $vendorId = $this->getVendorId();

        $vendor = $this->vendorModel->getProfileById($vendorId);

        if (!$vendor) {
            die('Vendor profile not found.');
        }

        // vnd_owner_name and vnd_profile_pic are now fetched directly by getProfileById()
        $vData = [
            'vnd_owner_name'  => $vendor['vnd_owner_name']  ?? null,
            'vnd_profile_pic' => $vendor['vnd_profile_pic'] ?? null,
        ];

        $this->view('vendor/profile', compact('vendor', 'vData'));
    }
}
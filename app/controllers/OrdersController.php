<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Vendor.php';

class OrdersController extends Controller
{
    private function requireBuyer(): int
    {
        if (!isset($_SESSION['user']['user_id'])) {
            header('Location: index.php?url=farmer');
            exit;
        }
        return (int)$_SESSION['user']['user_id'];
    }

    public function index()
    {
        $userId = $this->requireBuyer();

        $orderModel = new Order();
        $orders     = $orderModel->getByBuyer($userId);

        // Attach items to each order for expandable display
        foreach ($orders as &$order) {
            $order['items'] = $orderModel->getItemsByBuyer((int)$order['ord_id'], $userId);
        }
        unset($order);

        // Track which completed orders have already been reviewed
        $vendorModel   = new Vendor();
        $reviewedOrders = [];
        foreach ($orders as $order) {
            if (strtolower($order['ord_status']) === 'completed') {
                if ($vendorModel->hasReviewed($userId, (int)$order['ord_id'])) {
                    $reviewedOrders[] = (int)$order['ord_id'];
                }
            }
        }

        $this->view('buyer/order', [
            'orders'         => $orders,
            'reviewedOrders' => $reviewedOrders,
            'flash'          => $_SESSION['flash'] ?? null,
        ]);
        unset($_SESSION['flash']);
    }

    public function edit($orderId = null)
    {
        $userId  = $this->requireBuyer();
        $orderId = (int)($orderId ?? 0);

        if ($orderId <= 0) {
            header('Location: index.php?url=orders');
            exit;
        }

        $orderModel = new Order();
        $order      = $orderModel->getByIdForBuyer($orderId, $userId);

        if (!$order || !in_array(strtolower($order['ord_status']), ['pending', 'confirmed'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'This order cannot be edited.'];
            header('Location: index.php?url=orders');
            exit;
        }

        $this->view('buyer/edit-order', ['order' => $order, 'errors' => []]);
    }

    public function update($orderId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=orders');
            exit;
        }

        $userId  = $this->requireBuyer();
        $orderId = (int)($orderId ?? 0);

        if ($orderId <= 0) {
            header('Location: index.php?url=orders');
            exit;
        }

        $orderModel     = new Order();
        $order          = $orderModel->getByIdForBuyer($orderId, $userId);
        $errors         = [];

        if (!$order || !in_array(strtolower($order['ord_status']), ['pending', 'confirmed'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'This order cannot be edited.'];
            header('Location: index.php?url=orders');
            exit;
        }

        $deliveryMethod = $_POST['delivery_method'] ?? 'pickup';
        $deliveryAddr   = trim($_POST['delivery_address'] ?? '');
        $pickupSlot     = !empty($_POST['pickup_time_slot']) ? $_POST['pickup_time_slot'] : null;
        $notes          = trim($_POST['notes'] ?? '');

        if ($deliveryMethod === 'delivery' && $deliveryAddr === '') {
            $errors[] = 'Delivery address is required for delivery orders.';
        }

        if (!empty($errors)) {
            $this->view('buyer/edit-order', ['order' => $order, 'errors' => $errors]);
            return;
        }

        $ok = $orderModel->updateByBuyer($orderId, $userId, [
            'delivery_method'  => $deliveryMethod,
            'delivery_address' => $deliveryAddr ?: null,
            'pickup_time_slot' => $pickupSlot,
            'notes'            => $notes ?: null,
        ]);

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Order #' . $orderId . ' updated successfully.']
            : ['type' => 'error',   'message' => 'Could not update the order. Please try again.'];

        header('Location: index.php?url=orders');
        exit;
    }

    public function cancel($orderId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=orders');
            exit;
        }

        $userId  = $this->requireBuyer();
        $orderId = (int)($orderId ?? 0);

        if ($orderId <= 0) {
            header('Location: index.php?url=orders');
            exit;
        }

        $orderModel = new Order();
        $ok = $orderModel->updateStatusByBuyer($orderId, $userId, 'cancelled');

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Order #' . $orderId . ' has been cancelled.']
            : ['type' => 'error',   'message' => 'Unable to cancel order. It may have already been processed.'];

        header('Location: index.php?url=orders');
        exit;
    }

    public function received($orderId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=orders');
            exit;
        }

        $userId  = $this->requireBuyer();
        $orderId = (int)($orderId ?? 0);

        if ($orderId <= 0) {
            header('Location: index.php?url=orders');
            exit;
        }

        $orderModel = new Order();
        $ok = $orderModel->updateStatusByBuyer($orderId, $userId, 'completed');

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Order #' . $orderId . ' marked as received. Thank you!']
            : ['type' => 'error',   'message' => 'Unable to mark order as received. It may not be ready yet.'];

        header('Location: index.php?url=orders');
        exit;
    }

    public function review($orderId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?url=orders');
        }

        $userId  = $this->requireBuyer();
        $orderId = (int)($orderId ?? 0);

        if ($orderId <= 0) {
            $this->redirect('index.php?url=orders');
        }

        // Verify order belongs to buyer and is completed
        $orderModel = new Order();
        $order = $orderModel->getByIdForBuyer($orderId, $userId);

        if (!$order || strtolower($order['ord_status']) !== 'completed') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'You can only review completed orders.'];
            $this->redirect('index.php?url=orders');
        }

        $vendorModel = new Vendor();

        // Prevent duplicate reviews per order
        if ($vendorModel->hasReviewed($userId, $orderId)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'You have already reviewed this order.'];
            $this->redirect('index.php?url=orders');
        }

        $rating  = (int)($_POST['rating'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please select a rating between 1 and 5.'];
            $this->redirect('index.php?url=orders');
        }

        $ok = $vendorModel->addReview([
            'user_id'   => $userId,
            'vendor_id' => (int)$order['ord_vendor_id'],
            'order_id'  => $orderId,
            'rating'    => $rating,
            'title'     => $title ?: null,
            'comment'   => $comment ?: null,
        ]);

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'message' => 'Thank you for your review!']
            : ['type' => 'error',   'message' => 'Could not submit review. Please try again.'];

        $this->redirect('index.php?url=orders');
    }
}

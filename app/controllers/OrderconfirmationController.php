<?php

class OrderconfirmationController extends Controller
{
    public function index()
    {
        require_once __DIR__ . '/../models/Order.php';

        if (!isset($_SESSION['user']['user_id'])) {
            header('Location: index.php?url=farmer');
            exit;
        }

        $userId       = (int)$_SESSION['user']['user_id'];
        $lastOrderIds = $_SESSION['last_order_ids'] ?? [];

        if (empty($lastOrderIds)) {
            header('Location: index.php?url=orders');
            exit;
        }

        $orderModel = new Order();
        $orders     = $orderModel->getByIdsForBuyer($lastOrderIds, $userId);

        // Attach items to each order
        foreach ($orders as &$order) {
            $order['items'] = $orderModel->getItemsByBuyer((int)$order['ord_id'], $userId);
        }
        unset($order);

        // Clear so refresh doesn't re-show
        unset($_SESSION['last_order_ids']);

        $this->view('buyer/order_confirmation', [
            'orders' => $orders,
        ]);
    }
}

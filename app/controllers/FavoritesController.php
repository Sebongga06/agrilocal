<?php
require_once __DIR__ . '/../models/Favorite.php';

class FavoritesController extends Controller
{
    private function requireUser(): int
    {
        if (empty($_SESSION['user']['user_id'])) {
            header('Location: index.php?url=farmer');
            exit;
        }
        return (int)$_SESSION['user']['user_id'];
    }

    public function index()
    {
        $userId = $this->requireUser();
        $model  = new Favorite();
        $rows   = $model->getByUser($userId);

        $favorites = array_map(function ($row) {
            if (!empty($row['fav_product_id'])) {
                $images = json_decode($row['prd_images'] ?? '[]', true);
                $row['image'] = !empty($images[0])
                    ? $images[0]
                    : 'https://via.placeholder.com/400x300?text=Product';
                $row['type'] = 'product';
            } else {
                $row['image'] = !empty($row['fav_vnd_cover_photo'])
                    ? $row['fav_vnd_cover_photo']
                    : 'https://via.placeholder.com/400x300?text=Vendor';
                $row['type'] = 'vendor';
            }
            return $row;
        }, $rows);

        $this->view('buyer/favorites', [
            'favorites' => $favorites,
            'flash'     => $_SESSION['flash'] ?? null,
        ]);
        unset($_SESSION['flash']);
    }

    public function toggle()
    {
        $userId    = $this->requireUser();
        $isAjax    = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $vendorId  = !empty($_POST['vendor_id'])  ? (int)$_POST['vendor_id']  : null;

        if (!$productId && !$vendorId) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'No item specified']); exit; }
            header('Location: index.php?url=favorites');
            exit;
        }

        $model  = new Favorite();
        $result = $model->toggle($userId, $productId, $vendorId);

        if ($isAjax) {
            echo json_encode(['success' => $result['action'] !== 'error', 'action' => $result['action']]);
            exit;
        }

        if ($result['action'] === 'error') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not save favorite. Please try again.'];
        } else {
            $msg = $result['action'] === 'added' ? 'Added to favorites!' : 'Removed from favorites.';
            $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?url=favorites';
        header('Location: ' . $referer);
        exit;
    }

    public function remove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=favorites');
            exit;
        }

        $userId = $this->requireUser();
        $favId  = (int)($_POST['fav_id'] ?? 0);

        if ($favId > 0) {
            $model = new Favorite();
            $model->remove($favId, $userId);
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Removed from favorites.'];
        header('Location: index.php?url=favorites');
        exit;
    }
}

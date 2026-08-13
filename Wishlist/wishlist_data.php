<?php
ob_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$idsParam = trim($_GET['ids'] ?? '');
$ids = array_filter(array_map('intval', explode(',', $idsParam)));

$products = [];

if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    try {
        $stmt = $pdo->prepare("
            SELECT
                p.product_id,
                p.product_name,
                p.price,
                p.stock,
                p.status,
                c.category_name,
                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = p.product_id
                    ORDER BY pi.image_id ASC
                    LIMIT 1
                ) AS thumbnail
            FROM products p
            LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE p.product_id IN ($placeholders)
        ");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $products = [];
    }
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode($products);
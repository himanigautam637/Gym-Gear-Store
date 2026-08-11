<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'shop';

$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);

$products = [];
$categoryName = '';

try {
    $sql = "
        SELECT
            p.product_id,
            p.product_name,
            p.description,
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

        LEFT JOIN categories c
            ON c.category_id = p.category_id

        WHERE 1=1
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                LOWER(p.product_name) LIKE LOWER(?)
                OR LOWER(p.description) LIKE LOWER(?)
                OR LOWER(c.category_name) LIKE LOWER(?)
            )
        ";

        $searchTerm = '%' . $search . '%';

        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($categoryId > 0) {
        $sql .= " AND p.category_id = ? ";
        $params[] = $categoryId;
    }

    $sql .= "
        ORDER BY p.product_id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($categoryId > 0) {
        $categoryStmt = $pdo->prepare("
            SELECT category_name
            FROM categories
            WHERE category_id = ?
        ");

        $categoryStmt->execute([$categoryId]);

        $categoryName = $categoryStmt->fetchColumn() ?: '';
    }

} catch (PDOException $e) {
    $products = [];
    $categoryName = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Shop | Online Gym Gear Store</title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main>

    <section class="page-header">

        <div class="wrap">

            <div class="eyebrow">
                Our Collection
            </div>

            <h1>
                Shop Gym Gear
            </h1>

            <p>
                Explore all products available in our online gym gear store.
            </p>

        </div>

    </section>

    <section class="section">

        <div class="wrap">

            <?php if ($search !== ''): ?>

                <div class="section-head">

                    <div>

                        <div class="eyebrow">
                            Search Results
                        </div>

                        <h2>
                            <?= htmlspecialchars($search) ?>
                        </h2>

                        <p>
                            <?= count($products) ?> product(s) found
                        </p>

                    </div>

                    <a
                        href="/Gym-Gear-Store/shop.php"
                        class="btn-ghost"
                    >
                        Clear Search
                    </a>

                </div>

            <?php elseif ($categoryName !== ''): ?>

                <div class="section-head">

                    <div>

                        <div class="eyebrow">
                            Category
                        </div>

                        <h2>
                            <?= htmlspecialchars($categoryName) ?>
                        </h2>

                    </div>

                    <a
                        href="/Gym-Gear-Store/shop.php"
                        class="btn-ghost"
                    >
                        View All
                    </a>

                </div>

            <?php endif; ?>

            <?php if (empty($products)): ?>

                <div class="content-panel">

                    <h2>
                        No products found
                    </h2>

                    <?php if ($search !== ''): ?>

                        <p>
                            No products matching
                            "<?= htmlspecialchars($search) ?>"
                            were found.
                        </p>

                    <?php else: ?>

                        <p>
                            There are currently no products matching your selection.
                        </p>

                    <?php endif; ?>

                </div>

            <?php else: ?>

                <div class="prod-grid">

                    <?php foreach ($products as $p): ?>

                        <?php
                        include __DIR__ . '/partials/product-card.php';
                        ?>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

</body>
</html>
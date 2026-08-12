<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'shop';

$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';

$sortOptions = [
    'newest'     => 'p.product_id DESC',
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name_az'    => 'p.product_name ASC',
];

$orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];

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

    $sql .= " ORDER BY $orderBy ";

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

    <style>
        .sort-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        .sort-bar label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 700;
        }
        .sort-bar select {
            padding: 9px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--panel);
            color: var(--text);
            font-size: 13px;
        }
    </style>

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

            <form class="sort-bar" method="GET" action="/Gym-Gear-Store/shop.php" id="sortForm">
                <?php if ($search !== ''): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                <?php if ($categoryId > 0): ?><input type="hidden" name="category" value="<?= $categoryId ?>"><?php endif; ?>
                <label for="sortSelect">Sort by</label>
                <select id="sortSelect" name="sort" onchange="document.getElementById('sortForm').submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name_az" <?= $sort === 'name_az' ? 'selected' : '' ?>>Name: A to Z</option>
                </select>
            </form>

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

<?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
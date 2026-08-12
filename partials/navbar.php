<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$cartCount = 0;

if (isset($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
    $cartCount = array_sum($_SESSION['guest_cart']);
}

if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $cartStmt = $pdo->prepare("
            SELECT COALESCE(SUM(quantity), 0)
            FROM cart
            WHERE user_id = ?
        ");
        $cartStmt->execute([$_SESSION['user_id']]);
        $cartCount = (int)$cartStmt->fetchColumn();
    } catch (PDOException $e) {
        $cartCount = 0;
    }
}

$isCartPage = $currentPage === 'cart.php';

$searchValue = trim($_GET['search'] ?? '');
?>

<header class="site-header">
    <div class="header-inner">

        <a href="/Gym-Gear-Store/index.php" class="brand">

            <div class="brand-icon">
                <svg
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="#FFFFFF"
                    stroke-width="2.2"
                    stroke-linecap="round"
                >
                    <rect x="1.5" y="9" width="3" height="6" rx="1"></rect>
                    <rect x="19.5" y="9" width="3" height="6" rx="1"></rect>
                    <rect x="4.5" y="10.5" width="2.2" height="3" rx="0.5"></rect>
                    <rect x="17.3" y="10.5" width="2.2" height="3" rx="0.5"></rect>
                    <line x1="6.7" y1="12" x2="17.3" y2="12"></line>
                </svg>
            </div>

            <div>
                <div class="brand-name">ONLINE GYM GEAR</div>
                <div class="brand-tag">STORE</div>
            </div>

        </a>

        <nav class="main-nav">

            <a
                href="/Gym-Gear-Store/index.php"
                class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"
            >
                Home
            </a>

            <a
                href="/Gym-Gear-Store/shop.php"
                class="<?= $currentPage === 'shop.php' ? 'active' : '' ?>"
            >
                Shop
            </a>

            <a
                href="/Gym-Gear-Store/categories.php"
                class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>"
            >
                Categories
            </a>

            <a
                href="/Gym-Gear-Store/about.php"
                class="<?= $currentPage === 'about.php' ? 'active' : '' ?>"
            >
                About Us
            </a>

            <a
                href="/Gym-Gear-Store/contact.php"
                class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>"
            >
                Contact Us
            </a>

        </nav>

        <div class="header-actions">

            <form
                class="search-box"
                action="/Gym-Gear-Store/shop.php"
                method="GET"
            >
                <svg
                    width="15"
                    height="15"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M21 21l-4-4"/>
                </svg>

                <input
                    type="search"
                    name="search"
                    placeholder="Search gear..."
                    value="<?= htmlspecialchars($searchValue) ?>"
                    autocomplete="off"
                >
            </form>

            <a
                href="/Gym-Gear-Store/Wishlist/wishlist.php"
                class="icon-btn <?= $currentPage === 'wishlist.php' ? 'active-icon' : '' ?>"
                title="Wishlist"
            >
                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M20.8 4.6c-2-2-5.2-2-7.1 0L12 6.3l-1.7-1.7c-2-2-5.2-2-7.1 0-2 2-2 5.2 0 7.1L12 20.4l8.8-8.7c2-2 2-5.2 0-7.1z"/>
                </svg>

                <span class="icon-badge" id="wishlistBadge" style="display:none;"></span>
            </a>

            <a
                href="/Gym-Gear-Store/Cart/cart.php"
                class="icon-btn <?= $isCartPage ? 'active-icon' : '' ?>"
                title="Cart"
            >
                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M6 6h15l-1.5 9h-12z"/>
                    <path d="M6 6L5 2H2"/>
                    <circle cx="9" cy="20" r="1.3"/>
                    <circle cx="18" cy="20" r="1.3"/>
                </svg>

                <?php if ($cartCount > 0): ?>
                    <span class="icon-badge">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>
            </a>

            <a
                href="/Gym-Gear-Store/my_account.php"
                class="icon-btn <?= $currentPage === 'my_account.php' ? 'active-icon' : '' ?>"
                title="My Account"
            >
                <svg
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                </svg>
            </a>

        </div>

    </div>
</header>

<script src="/Gym-Gear-Store/partials/wishlist.js"></script>
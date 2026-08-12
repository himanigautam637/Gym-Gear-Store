<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'wishlist.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Wishlist | Online Gym Gear Store</title>
<link rel="stylesheet" href="/Gym-Gear-Store/partials/site.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/partials/navbar.php'; ?>

<main>

    <section class="page-header">
        <div class="wrap">
            <div class="eyebrow">Saved Gear</div>
            <h1>My Wishlist</h1>
            <p>Products you've saved for later, stored right in this browser.</p>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div id="wishlistContainer"></div>
        </div>
    </section>

</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/partials/footer.php'; ?>

</body>
</html>
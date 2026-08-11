<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$isLoggedIn = isset($_SESSION['user_id']);

$cartCount = 0;

if (isset($_SESSION['guest_cart'])) {
    foreach ($_SESSION['guest_cart'] as $qty) {
        $cartCount += (int)$qty;
    }
}

if ($isLoggedIn) {
    try {
        $cartStmt = $pdo->prepare("
            SELECT COALESCE(SUM(quantity), 0)
            FROM cart
            WHERE user_id = ?
        ");

        $cartStmt->execute([
            $_SESSION['user_id']
        ]);

        $cartCount = (int)$cartStmt->fetchColumn();

    } catch (PDOException $e) {
        $cartCount = 0;
    }
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

    <title>
        About Us | Gym Gear Store
    </title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

    <style>

        .about-page {
            width: 100%;
            background: #081b29;
            color: #ffffff;
        }

        .about-story {
            width: 100%;
            padding: 100px 5% 110px;
        }

        .about-content {
            max-width: 1050px;
            margin: 0 auto;
            text-align: center;
        }

        .about-eyebrow {
            color: #3f73e8;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 3px;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .about-content h1 {
            margin: 0;
            color: #ffffff;
            font-family: "Bricolage Grotesque", sans-serif;
            font-size: clamp(50px, 6vw, 82px);
            line-height: 1;
            letter-spacing: -3px;
            font-weight: 800;
        }

        .about-content p {
            max-width: 900px;
            margin: 42px auto 0;
            color: #91a4bd;
            font-size: 21px;
            line-height: 1.7;
        }

        .about-image {
            width: min(1700px, 100%);
            height: 520px;
            margin: 90px auto 0;
            overflow: hidden;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.08);
            background: #122a4a;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
        }

        .about-values {
            padding: 100px 5%;
            background: #0b2135;
        }

        .values-container {
            width: min(1100px, 100%);
            margin: auto;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 55px;
        }

        .section-heading .eyebrow {
            color: #3f73e8;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .section-heading h2 {
            margin: 0;
            color: #ffffff;
            font-family: "Bricolage Grotesque", sans-serif;
            font-size: 42px;
            line-height: 1.1;
        }

        .section-heading p {
            max-width: 650px;
            margin: 18px auto 0;
            color: #91a4bd;
            font-size: 16px;
            line-height: 1.7;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .value-card {
            padding: 30px;
            background: #122a4a;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 15px;
            transition: transform 0.25s ease,
                        border-color 0.25s ease;
        }

        .value-card:hover {
            transform: translateY(-5px);
            border-color: rgba(63,115,232,0.5);
        }

        .value-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            border-radius: 10px;
            background: rgba(63,115,232,0.15);
            color: #3f73e8;
            font-size: 22px;
            font-weight: 800;
        }

        .value-card h3 {
            margin: 0 0 12px;
            color: #ffffff;
            font-size: 19px;
        }

        .value-card p {
            margin: 0;
            color: #91a4bd;
            font-size: 14px;
            line-height: 1.7;
        }

        .about-stats {
            padding: 90px 5%;
            background: #081b29;
        }

        .stats-container {
            width: min(1050px, 100%);
            margin: auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat {
            text-align: center;
            padding: 25px 15px;
        }

        .stat-number {
            color: #3f73e8;
            font-family: "Bricolage Grotesque", sans-serif;
            font-size: 42px;
            font-weight: 800;
        }

        .stat-label {
            margin-top: 7px;
            color: #91a4bd;
            font-size: 13px;
        }

        .about-cta {
            padding: 90px 5%;
            background: #0b2135;
        }

        .cta-box {
            width: min(1000px, 100%);
            margin: auto;
            padding: 55px 40px;
            text-align: center;
            background: #122a4a;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
        }

        .cta-box h2 {
            margin: 0;
            color: #ffffff;
            font-family: "Bricolage Grotesque", sans-serif;
            font-size: 38px;
        }

        .cta-box p {
            max-width: 600px;
            margin: 15px auto 28px;
            color: #91a4bd;
            font-size: 15px;
            line-height: 1.7;
        }

        .shop-button {
            display: inline-block;
            padding: 13px 25px;
            background: #3f73e8;
            color: #ffffff;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: background 0.2s ease,
                        transform 0.2s ease;
        }

        .shop-button:hover {
            background: #5685ed;
            transform: translateY(-2px);
        }

        footer {
            background: #061522;
        }

        @media (max-width: 900px) {

            .about-story {
                padding: 75px 25px 90px;
            }

            .about-content h1 {
                font-size: 55px;
                letter-spacing: -2px;
            }

            .about-content p {
                font-size: 18px;
            }

            .about-image {
                height: 400px;
                margin-top: 65px;
                border-radius: 22px;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .about-values {
                padding: 75px 25px;
            }

            .about-stats {
                padding: 70px 25px;
            }
        }

        @media (max-width: 600px) {

            .about-story {
                padding: 60px 18px 70px;
            }

            .about-eyebrow {
                font-size: 12px;
                letter-spacing: 2px;
            }

            .about-content h1 {
                font-size: 40px;
                letter-spacing: -1px;
            }

            .about-content p {
                margin-top: 28px;
                font-size: 16px;
                line-height: 1.6;
            }

            .about-image {
                height: 270px;
                margin-top: 50px;
                border-radius: 18px;
            }

            .section-heading h2 {
                font-size: 32px;
            }

            .section-heading p {
                font-size: 14px;
            }

            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .stat-number {
                font-size: 32px;
            }

            .stat-label {
                font-size: 11px;
            }

            .cta-box {
                padding: 40px 22px;
            }

            .cta-box h2 {
                font-size: 30px;
            }
        }

    </style>

</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="about-page">

    <section class="about-story">

        <div class="about-content">

            <div class="about-eyebrow">
                OUR STORY
            </div>

            <h1>
                Built for athletes,<br>
                made for every workout.
            </h1>

            <p>
                Gym Gear Store provides reliable and quality fitness equipment
                for athletes, home gyms and fitness enthusiasts. From strength
                equipment to cardio gear and accessories, we make it easier
                to build the right setup for your training journey.
            </p>

        </div>

        <div class="about-image">

            <img
                src="/Gym-Gear-Store/about/images/about-gym.jpg"
                 alt="Modern gym equipment"
            >

        </div>

    </section>


    <section class="about-values">

        <div class="values-container">

            <div class="section-heading">

                <div class="eyebrow">
                    WHY GYM GEAR STORE
                </div>

                <h2>
                    Everything you need to train better.
                </h2>

                <p>
                    We focus on providing quality gym equipment,
                    simple shopping and dependable service for
                    every type of fitness journey.
                </p>

            </div>


            <div class="values-grid">

                <div class="value-card">

                    <div class="value-icon">
                        +
                    </div>

                    <h3>
                        Quality Equipment
                    </h3>

                    <p>
                        We offer reliable fitness equipment designed
                        to support your workouts and training goals.
                    </p>

                </div>


                <div class="value-card">

                    <div class="value-icon">
                        ✓
                    </div>

                    <h3>
                        Easy Shopping
                    </h3>

                    <p>
                        Browse products, explore categories and find
                        the equipment you need through a simple shopping
                        experience.
                    </p>

                </div>


                <div class="value-card">

                    <div class="value-icon">
                        ★
                    </div>

                    <h3>
                        Built for Training
                    </h3>

                    <p>
                        From home workouts to serious training,
                        our products are selected with fitness
                        and performance in mind.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <section class="about-stats">

        <div class="stats-container">

            <div class="stat">

                <div class="stat-number">
                    100+
                </div>

                <div class="stat-label">
                    Fitness Products
                </div>

            </div>


            <div class="stat">

                <div class="stat-number">
                    4+
                </div>

                <div class="stat-label">
                    Product Categories
                </div>

            </div>


            <div class="stat">

                <div class="stat-number">
                    24/7
                </div>

                <div class="stat-label">
                    Online Shopping
                </div>

            </div>


            <div class="stat">

                <div class="stat-number">
                    COD
                </div>

                <div class="stat-label">
                    Cash on Delivery
                </div>

            </div>

        </div>

    </section>


    <section class="about-cta">

        <div class="cta-box">

            <h2>
                Ready to upgrade your workout?
            </h2>

            <p>
                Explore our collection of gym equipment and
                accessories and find what you need for your
                next training session.
            </p>

            <a
                href="/Gym-Gear-Store/shop.php"
                class="shop-button"
            >
                Shop Now
            </a>

        </div>

    </section>

</main>


<footer>

    <div class="footer-top">

        <div>

            <div class="brand">

                <div class="brand-icon">
                    +
                </div>

                <div>

                    <div class="brand-name">
                        ONLINE GYM GEAR
                    </div>

                    <div class="brand-tag">
                        STORE
                    </div>

                </div>

            </div>

            <p class="footer-desc">
                Premium strength, cardio, accessories and
                supplements for your training journey.
            </p>

        </div>


        <div>

            <div class="footer-title">
                Shop
            </div>

            <div class="footer-links">

                <a href="/Gym-Gear-Store/shop.php">
                    Shop
                </a>

                <a href="/Gym-Gear-Store/categories.php">
                    Categories
                </a>

                <a href="/Gym-Gear-Store/Cart/cart.php">
                    My Cart
                </a>

            </div>

        </div>


        <div>

            <div class="footer-title">
                Account
            </div>

            <div class="footer-links">

                <a href="/Gym-Gear-Store/my_account.php">
                    My Account
                </a>

                <a href="/Gym-Gear-Store/Authentication/client_login.php">
                    Login
                </a>

                <a href="/Gym-Gear-Store/Authentication/client_register.php">
                    Register
                </a>

            </div>

        </div>


        <div>

            <div class="footer-title">
                Support
            </div>

            <div class="footer-links">

                <a href="/Gym-Gear-Store/about.php">
                    About Us
                </a>

                <a href="/Gym-Gear-Store/contact.php">
                    Contact Us
                </a>

            </div>

        </div>

    </div>


    <div class="footer-bottom">

        <span>

            &copy; <?= date('Y') ?>

            Online Gym Gear Store.

            All rights reserved.

        </span>

        <span>
            Cash on Delivery
        </span>

    </div>

</footer>

</body>

</html>
# Online Gym Gear Store

A full-stack e-commerce web application for browsing and ordering gym equipment, built with **pure HTML, CSS, JavaScript, and PHP** — no frameworks, no libraries, no build tools. Includes a complete client-facing storefront and a separate admin panel for managing the store.

Built as a 4th Semester  BCA project (Tribhuvan University).


## Tech Stack

- **Frontend:** HTML5, CSS3 (custom, no Bootstrap/Tailwind), vanilla JavaScript
- **Backend:** PHP (procedural, PDO for all database access)
- **Database:** MySQL / MariaDB
- **Email:** Gmail SMTP via a hand-written raw-socket mail client (no PHPMailer/Composer)
- **Environment:** XAMPP (Apache + MySQL + PHP)

No external JS/CSS frameworks or charting libraries are used anywhere in the project — the analytics charts, star ratings, and UI components are all built from scratch in plain CSS/JS.


## Features

### Client Side
- Home page with dynamic categories, best sellers, and new arrivals pulled live from the database
- Shop page with search, category filtering, and sorting (newest, price low–high, price high–low, name A–Z)
- Product detail page — image gallery, admin-written description, customer reviews, related products
- Cart system that works for **both guests and logged-in users** (guest carts live in the PHP session; logged-in carts are stored in the database and merged automatically on login)
- Quantity +/- steppers on product cards and the cart page (no page reload)
- Wishlist — heart-icon toggle stored in the browser's `localStorage`, with a dedicated wishlist page and a "Proceed to Order" bulk-checkout button
- Guest checkout gate — prompts login/registration mid-checkout without losing cart contents
- Cash-on-Delivery order placement, with real-time stock validation inside a database transaction
- Order confirmation page and a full order-history view with a 5-stage visual status tracker (Order Received → Confirmed → Packed → Shipped → Delivered)
- Product reviews — one review per user per product (star rating + comment), editable
- Contact form with server-side validation (letters-only name, valid email, digits-only phone)
- Registration with strong password rules (8+ characters, uppercase, lowercase, number, special character)

### Admin Side
- Secure admin login, separate session handling from client accounts
- Dashboard with key stats and clickable summary cards
- Category management (add/edit/delete, search)
- Product management (add/edit/delete, multiple image uploads per product, restock with price update, search/filter)
- Order management — update order status through 6 stages, automatically emails the customer on every status change
- Registered clients list
- Contact message inbox (reads from the same table the public contact form writes to)
- Analytics page — animated bar charts for orders and revenue over the last 30 days, with period-over-period trend indicators

---

## Database Overview

Key tables: `admin`, `users`, `categories`, `products`, `product_images`, `cart`, `orders`, `order_items`, `payments`, `reviews`, `contact_messages`.

- Products support **multiple images** via a separate `product_images` table
- Orders track a 6-stage `order_status` and a separate `payment_status`
- Reviews enforce **one review per user per product** with a `UNIQUE(product_id, user_id)` constraint

The full schema is in `Database/`.



## Setup

1. Clone or copy this project into your local server's web root (e.g. `htdocs/` for XAMPP).
2. Start Apache and MySQL.
3. Import the SQL file from `Database/` into phpMyAdmin or MySQL Workbench to create the database and tables.
4. Open `db_connect.php` and set your database name, username, and password.
5. Open `Admin/mail_config.php` and set your Gmail address + a Gmail **App Password** (not your regular password) if you want order-status emails to send.
6. Visit the project in your browser (e.g. `http://localhost/Gym-Gear-Store/`).



## System Workflow

**1. Store setup (Admin)**
The admin logs into `Admin/admin_dashboard.php`, creates product categories, then adds products with a name, description, price, stock quantity, and one or more images. Every product must belong to a category.

**2. Browsing (Client)**
Visitors can browse the home page or shop page without an account. Products can be searched, filtered by category, and sorted. Clicking a product opens its detail page with the full image gallery and the description the admin wrote.

**3. Cart**
Adding a product to the cart works whether the visitor is logged in or not — guests get a session-based cart, logged-in users get a database-backed cart. If a guest logs in mid-shopping, their session cart is automatically merged into their account cart.

**4. Checkout**
Clicking "Proceed to Order" leads to the checkout page. If the visitor isn't logged in, they're shown a login form right there (with a link to register) instead of losing their cart — once they log in, checkout resumes automatically. Logged-in users with items in their cart see an order summary and confirm with **Cash on Delivery**.

**5. Order placement**
Placing an order runs inside a database transaction: it re-validates stock for every item, creates the order and its line items, decrements product stock, marks any product that hits zero stock as "Out of Stock," clears the cart, and redirects to a confirmation page. If stock changed between viewing the cart and confirming, the order is safely rolled back with a clear error instead of overselling.

**6. Order fulfillment (Admin)**
The admin sees new orders in `Admin/manage_orders.php` and updates their status as they're processed — Pending → Confirmed → Packed → Shipped → Delivered (or Cancelled). Each status change automatically emails the customer with their updated order details, and the customer sees the same progress reflected as a visual tracker in "My Account."

**7. Reviews**
After interacting with a product, a logged-in customer can leave a star rating and comment on its detail page. Submitting a second review updates their existing one rather than creating a duplicate.

**8. Ongoing management**
The admin can restock products (updating both quantity and price in one action), respond to messages submitted through the public contact form, and monitor store performance — orders and revenue over the last 30 days — from the Analytics page.



## Notes

- All database queries use PDO prepared statements.
- Passwords (both admin and client) are hashed with PHP's `password_hash()`.
- Admin and client logins use separate session keys so logging out of one never affects the other.
- Currency is displayed in Nepali Rupees (Rs.).
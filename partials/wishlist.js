function getWishlist() {
    try {
        var raw = localStorage.getItem('gym_wishlist');
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        return [];
    }
}

function saveWishlist(list) {
    localStorage.setItem('gym_wishlist', JSON.stringify(list));
    updateWishlistBadge();
}

function isInWishlist(productId) {
    return getWishlist().indexOf(parseInt(productId, 10)) !== -1;
}

function toggleWishlist(productId, el) {
    productId = parseInt(productId, 10);
    var list = getWishlist();
    var index = list.indexOf(productId);

    if (index === -1) {
        list.push(productId);
        if (el) el.classList.add('active');
    } else {
        list.splice(index, 1);
        if (el) el.classList.remove('active');
    }

    saveWishlist(list);

    if (window.location.pathname.indexOf('wishlist.php') !== -1) {
        loadWishlistPage();
    }
}

function removeFromWishlist(productId) {
    var list = getWishlist().filter(function (id) {
        return id !== parseInt(productId, 10);
    });
    saveWishlist(list);
    loadWishlistPage();
}

function updateWishlistBadge() {
    var badge = document.getElementById('wishlistBadge');
    if (!badge) return;
    var count = getWishlist().length;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function applyWishlistHeartStates() {
    var hearts = document.querySelectorAll('.wishlist-heart[data-product-id]');
    hearts.forEach(function (heart) {
        var id = heart.getAttribute('data-product-id');
        if (isInWishlist(id)) {
            heart.classList.add('active');
        } else {
            heart.classList.remove('active');
        }
    });
}

function loadWishlistPage() {
    var container = document.getElementById('wishlistContainer');
    if (!container) return;

    var ids = getWishlist();

    if (ids.length === 0) {
        container.innerHTML = '<div class="empty-cart"><h2>Your wishlist is empty</h2><p>Tap the heart icon on any product to save it here.</p><a href="/Gym-Gear-Store/shop.php" class="checkout-btn">Start Shopping</a></div>';
        return;
    }

    container.innerHTML = '<div class="content-panel">Loading your wishlist...</div>';

    fetch('/Gym-Gear-Store/Wishlist/wishlist_data.php?ids=' + ids.join(','))
        .then(function (res) { return res.json(); })
        .then(function (products) {
            if (!products.length) {
                container.innerHTML = '<div class="empty-cart"><h2>Your wishlist is empty</h2><p>Tap the heart icon on any product to save it here.</p><a href="/Gym-Gear-Store/shop.php" class="checkout-btn">Start Shopping</a></div>';
                return;
            }

            var grid = document.createElement('div');
            grid.className = 'prod-grid';

            products.forEach(function (p) {
                var outOfStock = p.status === 'Out of Stock' || parseInt(p.stock, 10) <= 0;
                var card = document.createElement('div');
                card.className = 'prod-card';

                var imageHtml = p.thumbnail
                    ? '<img src="/Gym-Gear-Store/uploads/products/' + p.thumbnail + '" alt="' + p.product_name + '">'
                    : '<div class="placeholder-icon">+</div>';

                card.innerHTML =
                    '<div class="prod-image">' +
                        (outOfStock ? '<div class="stock-tag">Out of Stock</div>' : '') +
                        imageHtml +
                        '<button type="button" class="wishlist-heart active" onclick="removeFromWishlist(' + p.product_id + ')" title="Remove from wishlist">' +
                            '<svg viewBox="0 0 24 24" width="16" height="16"><path d="M12 21s-7.5-4.6-10-9.3C0.3 8 2 4 6 4c2 0 3.5 1 4.5 2.5C11.5 5 13 4 15 4c4 0 5.7 4 4 7.7C19.5 16.4 12 21 12 21z"/></svg>' +
                        '</button>' +
                    '</div>' +
                    '<div class="prod-body">' +
                        '<div class="prod-cat">' + (p.category_name || 'Gym Gear') + '</div>' +
                        '<div class="prod-name">' + p.product_name + '</div>' +
                        '<div class="prod-footer">' +
                            '<span class="prod-price">Rs. ' + Number(p.price).toFixed(2) + '</span>' +
                            (outOfStock
                                ? '<button type="button" class="btn-add" disabled>Add</button>'
                                : '<form action="/Gym-Gear-Store/Cart/add_to_cart.php" method="POST"><input type="hidden" name="product_id" value="' + p.product_id + '"><input type="hidden" name="quantity" value="1"><button type="submit" class="btn-add">Add</button></form>') +
                        '</div>' +
                    '</div>';

                grid.appendChild(card);
            });

            container.innerHTML = '';
            container.appendChild(grid);
        })
        .catch(function () {
            container.innerHTML = '<div class="content-panel"><h2>Could not load wishlist</h2><p>Please try again.</p></div>';
        });
}

document.addEventListener('DOMContentLoaded', function () {
    updateWishlistBadge();
    applyWishlistHeartStates();
    loadWishlistPage();
});
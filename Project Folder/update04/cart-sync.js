// cart-sync.js
// Shared cart sync logic for all pages

// Load cart from localStorage
function loadCartFromStorage() {
    try {
        const cart = localStorage.getItem('cartItems');
        return cart ? JSON.parse(cart) : [];
    } catch (e) {
        return [];
    }
}

// Save cart to localStorage and sync to server
function saveCartToStorage(cartItems) {
    localStorage.setItem('cartItems', JSON.stringify(cartItems));
    syncCartToServer(cartItems);
}

// Sync cart to PHP session via AJAX
function syncCartToServer(cartItems) {
    fetch('sync_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cart=' + encodeURIComponent(JSON.stringify(cartItems))
    });
}

// Update cart count UI (call this after any cart change)
function updateCartCountUI() {
    const cartItems = loadCartFromStorage();
    const cartCountElem = document.getElementById('cartCount');
    if (cartCountElem) {
        const total = cartItems.reduce((sum, item) => sum + (item.quantity || 1), 0);
        cartCountElem.textContent = total;
    }
}

// On page load, sync cart
(function() {
    const cartItems = loadCartFromStorage();
    syncCartToServer(cartItems);
    updateCartCountUI();
})();

// Usage:
// - Call loadCartFromStorage() to get cart
// - Call saveCartToStorage(cartItems) after any cart change
// - Call updateCartCountUI() after any cart change
// - Include this script in all pages with <script src="cart-sync.js"></script>

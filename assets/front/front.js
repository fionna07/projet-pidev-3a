document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart from localStorage
    let cart = JSON.parse(localStorage.getItem('equipementCart')) || [];
    updateCartDisplay();
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = parseFloat(this.getAttribute('data-price'));
            const image = this.getAttribute('data-image');
            
            // Check if item already in cart
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1
                });
            }
            
            // Save to localStorage and update display
            localStorage.setItem('equipementCart', JSON.stringify(cart));
            updateCartDisplay();
            
            // Show confirmation toast
            showToast(`${name} ajouté au panier`);
        });
    });
    
    // Clear cart button
    document.getElementById('clear-cart-btn').addEventListener('click', function() {
        cart = [];
        localStorage.setItem('equipementCart', JSON.stringify(cart));
        updateCartDisplay();
        showToast('Panier vidé');
    });
    

    
    // Search functionality
    document.getElementById('search-btn').addEventListener('click', filterEquipements);
    document.getElementById('search-input').addEventListener('keyup', filterEquipements);
    
    // Category filter
    document.getElementById('category-filter').addEventListener('change', filterEquipements);
    
    // Sort functionality
    document.getElementById('sort-by').addEventListener('change', sortEquipements);
    
    function updateCartDisplay() {
        const cartCount = document.getElementById('cart-count');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        
        // Update count
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
        
        // Update items list
        if (cart.length === 0) {
            cartItems.innerHTML = '<p class="text-center text-muted">Votre panier est vide</p>';
        } else {
            let itemsHtml = '';
            cart.forEach(item => {
                itemsHtml += `
                    <div class="cart-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="my-0">${item.name}</h6>
                                <small class="text-muted">${item.quantity} x ${item.price.toFixed(2)} €</small>
                            </div>
                            <span class="text-muted">${(item.price * item.quantity).toFixed(2)} €</span>
                        </div>
                        <div class="d-flex justify-content-end mt-1">
                            <button class="btn btn-sm btn-outline-danger remove-from-cart" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            cartItems.innerHTML = itemsHtml;
            
            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-from-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    cart = cart.filter(item => item.id !== id);
                    localStorage.setItem('equipementCart', JSON.stringify(cart));
                    updateCartDisplay();
                    showToast('Article retiré du panier');
                });
            });
        }
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = `${total.toFixed(2)} €`;
    }
    
    function filterEquipements() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
        
        document.querySelectorAll('.equipement-card').forEach(card => {
            const name = card.getAttribute('data-name');
            const category = card.getAttribute('data-category');
            
            const matchesSearch = name.includes(searchTerm);
            const matchesCategory = categoryFilter === '' || category === categoryFilter;
            
            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    function sortEquipements() {
        const sortBy = document.getElementById('sort-by').value;
        const container = document.getElementById('equipements-container');
        const cards = Array.from(container.querySelectorAll('.equipement-card'));
        
        cards.sort((a, b) => {
            switch(sortBy) {
                case 'price-asc':
                    return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
                case 'price-desc':
                    return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
                case 'name-asc':
                    return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                case 'name-desc':
                    return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                default:
                    return 0;
            }
        });
        
        // Re-append sorted cards
        cards.forEach(card => container.appendChild(card));
    }
    
    function showToast(message, type = 'success') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
    
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }
});
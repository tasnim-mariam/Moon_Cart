/**
 * MoonCart API Helper
 * Connect frontend to PHP backend
 */

const MoonCartAPI = {
    // Base URL for API - Change this when deploying
    BASE_URL: 'http://localhost/Moon_Cart/backend/api',

    /**
     * Generic fetch wrapper
     */
    async request(endpoint, options = {}) {
        const url = `${this.BASE_URL}/${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                try {
                    data = await response.json();
                } catch (jsonError) {
                    // If JSON parsing fails, create a generic error
                    throw new Error(`Server error (${response.status}). Please check if the database migration has been run.`);
                }
            } else {
                // If not JSON, read as text
                const text = await response.text();
                throw new Error(`Server error (${response.status}): ${text.substring(0, 200)}`);
            }
            
            if (!response.ok) {
                throw new Error(data.message || `API request failed (${response.status})`);
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // ==========================================
    // USER AUTHENTICATION
    // ==========================================

    /**
     * Login user
     */
    async login(email, password) {
        return this.request('users.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    },

    /**
     * Register new user
     */
    async register(userData) {
        return this.request('users.php?action=register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    },

    /**
     * Get user profile
     */
    async getProfile(userId) {
        return this.request(`users.php?action=profile&id=${userId}`);
    },

    /**
     * Update user profile
     */
    async updateProfile(userData) {
        return this.request('users.php?action=update', {
            method: 'PUT',
            body: JSON.stringify(userData)
        });
    },

    /**
     * Get all users (admin)
     */
    async getAllUsers(role = null) {
        const params = role ? `&role=${role}` : '';
        return this.request(`users.php?action=all${params}`);
    },

    // ==========================================
    // PRODUCTS
    // ==========================================

    /**
     * Get all products
     */
    async getProducts(limit = 50, offset = 0) {
        return this.request(`products.php?limit=${limit}&offset=${offset}`);
    },

    /**
     * Get single product
     */
    async getProduct(id) {
        return this.request(`products.php?action=single&id=${id}`);
    },

    /**
     * Get products by category
     */
    async getProductsByCategory(categorySlug) {
        return this.request(`products.php?action=category&category=${categorySlug}`);
    },

    /**
     * Search products
     */
    async searchProducts(query) {
        return this.request(`products.php?action=search&q=${encodeURIComponent(query)}`);
    },

    /**
     * Create product (admin)
     */
    async createProduct(productData) {
        return this.request('products.php', {
            method: 'POST',
            body: JSON.stringify(productData)
        });
    },

    /**
     * Update product (admin)
     */
    async updateProduct(productData) {
        return this.request('products.php', {
            method: 'PUT',
            body: JSON.stringify(productData)
        });
    },

    /**
     * Delete product (admin)
     */
    async deleteProduct(id) {
        return this.request(`products.php?id=${id}`, {
            method: 'DELETE'
        });
    },

    // ==========================================
    // CATEGORIES
    // ==========================================

    /**
     * Get all categories
     */
    async getCategories() {
        return this.request('categories.php');
    },

    /**
     * Get category with products
     */
    async getCategory(id) {
        return this.request(`categories.php?action=single&id=${id}`);
    },

    /**
     * Create category (admin)
     */
    async createCategory(categoryData) {
        return this.request('categories.php', {
            method: 'POST',
            body: JSON.stringify(categoryData)
        });
    },

    /**
     * Update category (admin)
     */
    async updateCategory(categoryData) {
        return this.request('categories.php', {
            method: 'PUT',
            body: JSON.stringify(categoryData)
        });
    },

    /**
     * Delete category (admin)
     */
    async deleteCategory(id) {
        return this.request(`categories.php?id=${id}`, {
            method: 'DELETE'
        });
    },

    // ==========================================
    // ORDERS
    // ==========================================

    /**
     * Get all orders (admin)
     */
    async getAllOrders(status = null, limit = 50, offset = 0) {
        let url = `orders.php?limit=${limit}&offset=${offset}`;
        if (status) url += `&status=${status}`;
        return this.request(url);
    },

    /**
     * Get single order
     */
    async getOrder(id) {
        return this.request(`orders.php?action=single&id=${id}`);
    },

    /**
     * Get user orders
     */
    async getUserOrders(userId) {
        return this.request(`orders.php?action=user&user_id=${userId}`);
    },

    /**
     * Get order statistics (admin dashboard)
     */
    async getOrderStats() {
        return this.request('orders.php?action=stats');
    },

    /**
     * Create new order
     */
    async createOrder(orderData) {
        return this.request('orders.php', {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
    },

    /**
     * Update order status (admin)
     */
    async updateOrderStatus(orderId, status, options = {}) {
        // Ensure orderId and status are valid
        if (!orderId || !status) {
            throw new Error('Order ID and status are required');
        }
        
        const data = { 
            id: String(orderId).trim(), 
            status: String(status).trim() 
        };
        
        if (options.delivery_man_id) data.delivery_man_id = options.delivery_man_id;
        if (options.estimated_delivery_time) data.estimated_delivery_time = options.estimated_delivery_time;
        if (options.cancellation_reason) data.cancellation_reason = options.cancellation_reason;
        
        // Debug log
        console.log('Updating order status:', data);
        
        return this.request('orders.php?action=status', {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    // ==========================================
    // ADDRESSES
    // ==========================================

    /**
     * Get user addresses
     */
    async getAddresses(userId) {
        return this.request(`addresses.php?user_id=${userId}`);
    },

    /**
     * Add new address
     */
    async addAddress(addressData) {
        return this.request('addresses.php', {
            method: 'POST',
            body: JSON.stringify(addressData)
        });
    },

    /**
     * Update address
     */
    async updateAddress(addressData) {
        return this.request('addresses.php', {
            method: 'PUT',
            body: JSON.stringify(addressData)
        });
    },

    /**
     * Set default address
     */
    async setDefaultAddress(addressId, userId) {
        return this.request('addresses.php?action=default', {
            method: 'PUT',
            body: JSON.stringify({ id: addressId, user_id: userId })
        });
    },

    /**
     * Delete address
     */
    async deleteAddress(id) {
        return this.request(`addresses.php?id=${id}`, {
            method: 'DELETE'
        });
    },

    // ==========================================
    // PRODUCT REQUESTS
    // ==========================================

    /**
     * Get all product requests (admin)
     */
    async getProductRequests(status = null) {
        const params = status ? `?status=${status}` : '';
        return this.request(`product_requests.php${params}`);
    },

    /**
     * Get user's product requests
     */
    async getUserProductRequests(userId) {
        return this.request(`product_requests.php?action=user&user_id=${userId}`);
    },

    /**
     * Submit product request
     */
    async submitProductRequest(requestData) {
        return this.request('product_requests.php', {
            method: 'POST',
            body: JSON.stringify(requestData)
        });
    },

    /**
     * Update request status (admin)
     */
    async updateProductRequestStatus(requestId, status, adminNotes = null) {
        return this.request('product_requests.php', {
            method: 'PUT',
            body: JSON.stringify({ id: requestId, status, admin_notes: adminNotes })
        });
    },

    // ==========================================
    // CONTACT MESSAGES
    // ==========================================

    /**
     * Get all contact messages (admin)
     */
    async getContactMessages(unreadOnly = false) {
        const params = unreadOnly ? '?unread=true' : '';
        return this.request(`contact.php${params}`);
    },

    /**
     * Submit contact message
     */
    async submitContactMessage(messageData) {
        return this.request('contact.php', {
            method: 'POST',
            body: JSON.stringify(messageData)
        });
    },

    /**
     * Mark message as read (admin)
     */
    async markMessageAsRead(messageId) {
        return this.request('contact.php?action=read', {
            method: 'PUT',
            body: JSON.stringify({ id: messageId })
        });
    },

    /**
     * Delete message (admin)
     */
    async deleteContactMessage(id) {
        return this.request(`contact.php?id=${id}`, {
            method: 'DELETE'
        });
    },

    // ==========================================
    // CART (User-specific)
    // ==========================================

    /**
     * Get user's cart
     */
    async getCart(userId) {
        return this.request(`cart.php?user_id=${userId}`);
    },

    /**
     * Add item to cart
     */
    async addToCart(userId, productId, quantity = 1, category = 'Product') {
        return this.request('cart.php', {
            method: 'POST',
            body: JSON.stringify({
                user_id: userId,
                product_id: productId,
                quantity: quantity,
                category: category
            })
        });
    },

    /**
     * Update cart item quantity
     */
    async updateCartItem(userId, productId, change) {
        return this.request('cart.php', {
            method: 'PUT',
            body: JSON.stringify({
                user_id: userId,
                product_id: productId,
                change: change
            })
        });
    },

    /**
     * Remove item from cart
     */
    async removeFromCart(userId, productId) {
        return this.request(`cart.php?user_id=${userId}&product_id=${productId}`, {
            method: 'DELETE'
        });
    },

    /**
     * Clear entire cart
     */
    async clearCart(userId) {
        return this.request(`cart.php?action=clear&user_id=${userId}`, {
            method: 'DELETE'
        });
    },

    // ==========================================
    // DELIVERY MEN
    // ==========================================

    /**
     * Get all delivery men
     */
    async getDeliveryMen(activeOnly = false) {
        const params = activeOnly ? '?active_only=true' : '';
        return this.request(`delivery_men.php${params}`);
    },

    /**
     * Get single delivery man
     */
    async getDeliveryMan(id) {
        return this.request(`delivery_men.php?action=single&id=${id}`);
    },

    /**
     * Create delivery man
     */
    async createDeliveryMan(deliveryManData) {
        return this.request('delivery_men.php', {
            method: 'POST',
            body: JSON.stringify(deliveryManData)
        });
    },

    /**
     * Update delivery man
     */
    async updateDeliveryMan(deliveryManData) {
        return this.request('delivery_men.php', {
            method: 'PUT',
            body: JSON.stringify(deliveryManData)
        });
    },

    /**
     * Delete delivery man
     */
    async deleteDeliveryMan(id) {
        return this.request(`delivery_men.php?id=${id}`, {
            method: 'DELETE'
        });
    }
};

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MoonCartAPI;
}


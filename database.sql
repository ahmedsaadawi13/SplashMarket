-- FILE: /database.sql

-- SplashMarket - Multi-Tenant Marketplace SaaS
-- Complete Database Schema with Seed Data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS splashmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splashmarket;

-- ============================================================
-- CORE TABLES
-- ============================================================

-- Plans Table
CREATE TABLE plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    billing_period VARCHAR(20) NOT NULL DEFAULT 'monthly',
    max_products INT NOT NULL DEFAULT 100,
    max_orders_per_month INT NOT NULL DEFAULT 1000,
    max_storage_mb INT NOT NULL DEFAULT 1000,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenants Table
CREATE TABLE tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    store_name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100),
    logo VARCHAR(255),
    banner_image VARCHAR(255),
    store_description TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    currency VARCHAR(10) DEFAULT 'USD',
    theme_color VARCHAR(20),
    meta_title VARCHAR(255),
    meta_description TEXT,
    footer_text TEXT,
    api_key VARCHAR(100) UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    INDEX idx_active (is_active),
    INDEX idx_subdomain (subdomain),
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Table
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('platform_admin', 'tenant_admin', 'staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_email (email),
    INDEX idx_tenant (tenant_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant Subscriptions Table
CREATE TABLE tenant_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    status ENUM('active', 'canceled', 'expired') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CATALOG TABLES
-- ============================================================

-- Categories Table
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    compare_at_price DECIMAL(10,2),
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_on_sale TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_category (category_id),
    INDEX idx_sku (sku),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock History Table
CREATE TABLE stock_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED,
    old_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    change_amount INT NOT NULL,
    reason VARCHAR(255),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CUSTOMER TABLES
-- ============================================================

-- Customers Table
CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    password VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Addresses Table
CREATE TABLE addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    type ENUM('billing', 'shipping', 'both') NOT NULL DEFAULT 'both',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    country VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ORDER TABLES
-- ============================================================

-- Orders Table
CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_email VARCHAR(255) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'completed', 'canceled', 'refunded') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_customer (customer_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cart Items Table
CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- COUPON TABLES
-- ============================================================

-- Coupons Table
CREATE TABLE coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2),
    max_uses INT,
    times_used INT NOT NULL DEFAULT 0,
    start_date DATETIME,
    end_date DATETIME,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BILLING TABLES
-- ============================================================

-- Invoices Table
CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    subscription_id INT UNSIGNED,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    paid_at DATETIME,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES tenant_subscriptions(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    transaction_id VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_invoice (invoice_id),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATION TABLES
-- ============================================================

-- Notifications Table
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    type VARCHAR(50) NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    related_id INT UNSIGNED,
    status ENUM('sent', 'failed') NOT NULL DEFAULT 'sent',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Insert Plans
INSERT INTO plans (name, slug, description, price, max_products, max_orders_per_month, max_storage_mb, created_at) VALUES
('Free', 'free', 'Perfect for getting started', 0.00, 10, 50, 100, NOW()),
('Starter', 'starter', 'For small businesses', 29.99, 100, 500, 1000, NOW()),
('Professional', 'professional', 'For growing businesses', 79.99, 500, 2000, 5000, NOW()),
('Enterprise', 'enterprise', 'For large businesses', 199.99, 9999, 99999, 50000, NOW());

-- Insert Platform Admin User
INSERT INTO users (tenant_id, name, email, password, role, created_at) VALUES
(NULL, 'Platform Admin', 'admin@splashmarket.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', NOW());
-- Password: password

-- Insert Demo Tenants
INSERT INTO tenants (store_name, subdomain, contact_email, currency, theme_color, api_key, created_at) VALUES
('TechGadgets Store', 'techgadgets', 'contact@techgadgets.com', 'USD', '#007bff', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', NOW()),
('Fashion Hub', 'fashionhub', 'hello@fashionhub.com', 'USD', '#e91e63', 'ca978112ca1bbdcafac231b39a23dc4da786eff8147c4e72b9807785afee48bb', NOW());

-- Insert Tenant Admin Users
INSERT INTO users (tenant_id, name, email, password, role, created_at) VALUES
(1, 'Tech Admin', 'admin@techgadgets.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', NOW()),
(2, 'Fashion Admin', 'admin@fashionhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', NOW());

-- Insert Subscriptions
INSERT INTO tenant_subscriptions (tenant_id, plan_id, start_date, status, created_at) VALUES
(1, 2, NOW(), 'active', NOW()),
(2, 3, NOW(), 'active', NOW());

-- Insert Categories for Tenant 1 (TechGadgets)
INSERT INTO categories (tenant_id, name, slug, description, is_active, created_at) VALUES
(1, 'Smartphones', 'smartphones', 'Latest smartphones and accessories', 1, NOW()),
(1, 'Laptops', 'laptops', 'Powerful laptops for work and gaming', 1, NOW()),
(1, 'Accessories', 'accessories', 'Tech accessories and gadgets', 1, NOW());

-- Insert Categories for Tenant 2 (Fashion Hub)
INSERT INTO categories (tenant_id, name, slug, description, is_active, created_at) VALUES
(2, 'Men', 'men', 'Men\'s fashion and clothing', 1, NOW()),
(2, 'Women', 'women', 'Women\'s fashion and clothing', 1, NOW()),
(2, 'Accessories', 'accessories', 'Fashion accessories', 1, NOW());

-- Insert Products for Tenant 1
INSERT INTO products (tenant_id, category_id, name, slug, sku, description, price, compare_at_price, stock_quantity, status, is_featured, created_at) VALUES
(1, 1, 'iPhone 14 Pro', 'iphone-14-pro', 'IP14PRO-128', 'Latest iPhone with advanced camera system', 999.99, 1099.99, 50, 'published', 1, NOW()),
(1, 1, 'Samsung Galaxy S23', 'samsung-galaxy-s23', 'SGS23-256', 'Flagship Android smartphone', 899.99, 999.99, 30, 'published', 1, NOW()),
(1, 2, 'MacBook Pro 14"', 'macbook-pro-14', 'MBP14-M2', 'Powerful laptop with M2 chip', 1999.99, NULL, 20, 'published', 1, NOW()),
(1, 2, 'Dell XPS 15', 'dell-xps-15', 'DXPS15-i7', 'Premium Windows laptop', 1599.99, 1799.99, 15, 'published', 0, NOW()),
(1, 3, 'AirPods Pro', 'airpods-pro', 'APP-2GEN', 'Wireless earbuds with noise cancellation', 249.99, NULL, 100, 'published', 1, NOW()),
(1, 3, 'USB-C Hub', 'usbc-hub', 'USBHUB-7P', '7-port USB-C hub adapter', 49.99, 59.99, 0, 'published', 0, NOW());

-- Insert Products for Tenant 2
INSERT INTO products (tenant_id, category_id, name, slug, sku, description, price, compare_at_price, stock_quantity, status, is_featured, created_at) VALUES
(2, 4, 'Classic Leather Jacket', 'classic-leather-jacket', 'CLJ-BLK-L', 'Genuine leather jacket for men', 199.99, 249.99, 25, 'published', 1, NOW()),
(2, 4, 'Denim Jeans', 'denim-jeans', 'DJ-BLU-32', 'Classic blue denim jeans', 59.99, NULL, 50, 'published', 0, NOW()),
(2, 5, 'Summer Dress', 'summer-dress', 'SD-FLR-M', 'Floral print summer dress', 79.99, 99.99, 40, 'published', 1, NOW()),
(2, 5, 'Evening Gown', 'evening-gown', 'EG-BLK-S', 'Elegant black evening gown', 299.99, NULL, 10, 'published', 1, NOW()),
(2, 6, 'Designer Handbag', 'designer-handbag', 'DHB-LTR-BRN', 'Luxury leather handbag', 399.99, 499.99, 15, 'published', 1, NOW());

-- Insert Customers
INSERT INTO customers (tenant_id, first_name, last_name, email, phone, created_at) VALUES
(1, 'John', 'Doe', 'john.doe@example.com', '+1234567890', NOW()),
(1, 'Jane', 'Smith', 'jane.smith@example.com', '+1234567891', NOW()),
(2, 'Alice', 'Johnson', 'alice.j@example.com', '+1234567892', NOW()),
(2, 'Bob', 'Williams', 'bob.w@example.com', '+1234567893', NOW());

-- Insert Sample Orders
INSERT INTO orders (tenant_id, customer_id, order_number, customer_email, subtotal, shipping_cost, total_amount, status, payment_method, created_at) VALUES
(1, 1, 'ORD-20250122-00001', 'john.doe@example.com', 1249.98, 10.00, 1259.98, 'completed', 'online', NOW() - INTERVAL 5 DAY),
(1, 2, 'ORD-20250122-00002', 'jane.smith@example.com', 249.99, 10.00, 259.99, 'shipped', 'cod', NOW() - INTERVAL 2 DAY),
(2, 3, 'ORD-20250122-00003', 'alice.j@example.com', 479.98, 10.00, 489.98, 'processing', 'online', NOW() - INTERVAL 1 DAY),
(2, 4, 'ORD-20250122-00004', 'bob.w@example.com', 59.99, 10.00, 69.99, 'pending', 'cod', NOW());

-- Insert Order Items
INSERT INTO order_items (order_id, product_id, product_name, sku, price, quantity, line_total) VALUES
(1, 1, 'iPhone 14 Pro', 'IP14PRO-128', 999.99, 1, 999.99),
(1, 5, 'AirPods Pro', 'APP-2GEN', 249.99, 1, 249.99),
(2, 5, 'AirPods Pro', 'APP-2GEN', 249.99, 1, 249.99),
(3, 7, 'Classic Leather Jacket', 'CLJ-BLK-L', 199.99, 1, 199.99),
(3, 9, 'Summer Dress', 'SD-FLR-M', 79.99, 1, 79.99),
(3, 11, 'Designer Handbag', 'DHB-LTR-BRN', 399.99, 1, 399.99),
(4, 8, 'Denim Jeans', 'DJ-BLU-32', 59.99, 1, 59.99);

-- Insert Sample Coupons
INSERT INTO coupons (tenant_id, code, discount_type, discount_value, min_order_amount, max_uses, start_date, end_date, created_at) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 50.00, 100, NOW(), NOW() + INTERVAL 30 DAY, NOW()),
(1, 'SAVE50', 'fixed', 50.00, 500.00, 50, NOW(), NOW() + INTERVAL 30 DAY, NOW()),
(2, 'FASHION20', 'percentage', 20.00, 100.00, 200, NOW(), NOW() + INTERVAL 30 DAY, NOW());

-- Insert Sample Invoices
INSERT INTO invoices (tenant_id, subscription_id, invoice_number, amount, status, paid_at, created_at) VALUES
(1, 1, 'INV-20250101-00001', 29.99, 'paid', NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 20 DAY),
(2, 2, 'INV-20250101-00002', 79.99, 'paid', NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 20 DAY);

-- Insert Sample Payments
INSERT INTO payments (tenant_id, invoice_id, transaction_id, amount, payment_method, status, created_at) VALUES
(1, 1, 'TXN-0A1B2C3D4E5F6A7B8C9D0E1F2A3B4C5D', 29.99, 'dummy', 'completed', NOW() - INTERVAL 20 DAY),
(2, 2, 'TXN-1B2C3D4E5F6A7B8C9D0E1F2A3B4C5D6E', 79.99, 'dummy', 'completed', NOW() - INTERVAL 20 DAY);


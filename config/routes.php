<?php
// FILE: /config/routes.php

/**
 * SplashMarket - Route Definitions
 *
 * Define all application routes
 * PHP 7.0+ compatible
 */

// Authentication routes
$router->get('/login', 'AuthController@showLogin', 'login');
$router->post('/login', 'AuthController@login', 'login.post');
$router->get('/logout', 'AuthController@logout', 'logout');
$router->get('/register', 'AuthController@showRegister', 'register');
$router->post('/register', 'AuthController@register', 'register.post');

// Dashboard
$router->get('/', 'DashboardController@index', 'home');
$router->get('/dashboard', 'DashboardController@index', 'dashboard');

// Products
$router->get('/products', 'ProductController@index', 'products.index');
$router->get('/products/create', 'ProductController@create', 'products.create');
$router->post('/products/create', 'ProductController@store', 'products.store');
$router->get('/products/:id/edit', 'ProductController@edit', 'products.edit');
$router->post('/products/:id/edit', 'ProductController@update', 'products.update');
$router->post('/products/:id/delete', 'ProductController@delete', 'products.delete');

// Categories
$router->get('/categories', 'CategoryController@index', 'categories.index');
$router->get('/categories/create', 'CategoryController@create', 'categories.create');
$router->post('/categories/create', 'CategoryController@store', 'categories.store');
$router->get('/categories/:id/edit', 'CategoryController@edit', 'categories.edit');
$router->post('/categories/:id/edit', 'CategoryController@update', 'categories.update');
$router->post('/categories/:id/delete', 'CategoryController@delete', 'categories.delete');

// Orders
$router->get('/orders', 'OrderController@index', 'orders.index');
$router->get('/orders/:id', 'OrderController@view', 'orders.view');
$router->post('/orders/:id/status', 'OrderController@updateStatus', 'orders.status');
$router->get('/orders/:id/invoice', 'OrderController@invoice', 'orders.invoice');

// Customers
$router->get('/customers', 'CustomerController@index', 'customers.index');
$router->get('/customers/create', 'CustomerController@create', 'customers.create');
$router->post('/customers/create', 'CustomerController@store', 'customers.store');
$router->get('/customers/:id', 'CustomerController@view', 'customers.view');
$router->get('/customers/:id/edit', 'CustomerController@edit', 'customers.edit');
$router->post('/customers/:id/edit', 'CustomerController@update', 'customers.update');

// Store settings
$router->get('/store/settings', 'StoreController@settings', 'store.settings');
$router->post('/store/settings', 'StoreController@updateSettings', 'store.settings.update');
$router->get('/store/api-key', 'StoreController@apiKey', 'store.api');
$router->post('/store/api-key/regenerate', 'StoreController@regenerateApiKey', 'store.api.regenerate');

// Coupons
$router->get('/coupons', 'CouponController@index', 'coupons.index');
$router->get('/coupons/create', 'CouponController@create', 'coupons.create');
$router->post('/coupons/create', 'CouponController@store', 'coupons.store');
$router->get('/coupons/:id/edit', 'CouponController@edit', 'coupons.edit');
$router->post('/coupons/:id/edit', 'CouponController@update', 'coupons.update');
$router->post('/coupons/:id/delete', 'CouponController@delete', 'coupons.delete');

// Subscription
$router->get('/subscription', 'SubscriptionController@index', 'subscription.index');
$router->get('/subscription/change-plan', 'SubscriptionController@changePlan', 'subscription.change');
$router->post('/subscription/update-plan', 'SubscriptionController@updatePlan', 'subscription.update');
$router->get('/subscription/payment/:id', 'SubscriptionController@payment', 'subscription.payment');
$router->post('/subscription/payment/:id', 'SubscriptionController@processPayment', 'subscription.process');

// Platform Admin
$router->get('/platform/dashboard', 'PlatformController@dashboard', 'platform.dashboard');
$router->get('/platform/tenants', 'PlatformController@tenants', 'platform.tenants');
$router->get('/platform/tenants/:id', 'PlatformController@tenantView', 'platform.tenants.view');
$router->post('/platform/tenants/:id/toggle', 'PlatformController@toggleTenant', 'platform.tenants.toggle');
$router->get('/platform/plans', 'PlatformController@plans', 'platform.plans');

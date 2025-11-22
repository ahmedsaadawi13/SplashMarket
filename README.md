# SplashMarket

A complete multi-tenant SaaS marketplace platform built with pure PHP and MySQL. Each tenant can run their own online store with full e-commerce functionality.

## Features

### Multi-Tenant Architecture
- Isolated data per tenant (vendor/store)
- Subscription-based access with usage limits
- Platform admin dashboard for managing all tenants
- API key authentication per tenant

### Store Management
- Customizable store settings (name, logo, colors, SEO)
- Subdomain support (configurable)
- Storefront customization

### Catalog Management
- Products with variants, pricing, and inventory
- Hierarchical categories
- Product images and galleries
- Stock tracking with history
- Featured and sale products

### Shopping & Checkout
- Public storefront for each tenant
- Shopping cart functionality
- Guest and registered customer checkout
- Multiple payment methods (COD, dummy online payment)
- Coupon/discount system

### Order Management
- Complete order workflow
- Status management (pending, confirmed, processing, shipped, completed, canceled, refunded)
- Order invoicing
- Customer order history

### Customer Management
- Customer accounts with addresses
- Order history tracking
- Customer analytics

### Subscription & Billing
- Multiple subscription plans with limits
- Usage tracking (products, orders, storage)
- Invoicing and payment records
- Plan upgrades/downgrades

### Public API
- Product listing endpoint (with filtering, search, pagination)
- Order creation endpoint for external integrations
- API key authentication

## Technical Stack

- **Backend**: PHP 7.0+ (no frameworks)
- **Database**: MySQL 5.7+ with InnoDB
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Architecture**: Custom MVC pattern

## Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL, GD (optional for image processing)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd SplashMarket
```

### 2. Database Setup

Create a MySQL database and import the schema:

```bash
mysql -u root -p
CREATE DATABASE splashmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

mysql -u root -p splashmarket < database.sql
```

### 3. Configuration

Copy the environment example file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_NAME=splashmarket
DB_USER=root
DB_PASS=your_password
```

### 4. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
chmod -R 755 storage/logs/
```

### 5. Web Server Configuration

#### Apache

The `.htaccess` file is already configured. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

Configure your virtual host:

```apache
<VirtualHost *:80>
    ServerName splashmarket.local
    DocumentRoot /path/to/SplashMarket/public

    <Directory /path/to/SplashMarket/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashmarket-error.log
    CustomLog ${APACHE_LOG_DIR}/splashmarket-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name splashmarket.local;
    root /path/to/SplashMarket/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 6. Access the Application

- **Admin Panel**: http://your-domain.com/
- **Storefront**: http://your-domain.com/store/?tenant_id=1
- **API**: http://your-domain.com/api/

## Default Login Credentials

### Platform Admin
- Email: admin@splashmarket.com
- Password: password

### Tenant Admins
- TechGadgets: admin@techgadgets.com / password
- Fashion Hub: admin@fashionhub.com / password

## API Documentation

### Authentication

All API requests require an API key in the header:

```
X-API-KEY: your_tenant_api_key
```

Or as a query parameter:

```
?api_key=your_tenant_api_key
```

### Endpoints

#### GET /api/products

List all published products for a tenant.

**Query Parameters:**
- `category_id` (optional): Filter by category
- `search` (optional): Search in name/description
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20, max: 100)

**Example Request:**

```bash
curl -H "X-API-KEY: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855" \
     http://your-domain.com/api/products?page=1&per_page=10
```

**Example Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "iPhone 14 Pro",
      "slug": "iphone-14-pro",
      "sku": "IP14PRO-128",
      "description": "Latest iPhone...",
      "price": "999.99",
      "compare_at_price": "1099.99",
      "stock_quantity": 50,
      "in_stock": true,
      "image": "products/...",
      "is_featured": true,
      "is_on_sale": false,
      "category_id": 1
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 50,
    "total_pages": 5
  }
}
```

#### GET /api/products/:id

Get single product details.

**Example Request:**

```bash
curl -H "X-API-KEY: your_api_key" \
     http://your-domain.com/api/products/1
```

#### POST /api/orders

Create a new order.

**Request Body:**

```json
{
  "customer_email": "customer@example.com",
  "customer_name": "John Doe",
  "customer_phone": "+1234567890",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ],
  "shipping_address": {
    "address": "123 Main St",
    "city": "New York",
    "postal_code": "10001"
  },
  "payment_method": "cod",
  "shipping_cost": 10.00
}
```

**Example Request:**

```bash
curl -X POST \
     -H "X-API-KEY: your_api_key" \
     -H "Content-Type: application/json" \
     -d '{"customer_email":"test@example.com","items":[{"product_id":1,"quantity":1}]}' \
     http://your-domain.com/api/orders
```

**Example Response:**

```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order_id": 123,
    "order_number": "ORD-20250122-00123",
    "status": "pending",
    "total": "1009.99"
  }
}
```

## Testing

Run the functional tests:

```bash
php tests/functional_tests.php
```

## Project Structure

```
SplashMarket/
├── app/
│   ├── controllers/        # Application controllers
│   ├── core/              # Core MVC classes
│   ├── helpers/           # Helper functions
│   ├── models/            # Data models
│   └── views/             # View templates
├── config/                # Configuration files
├── public/                # Public web root
│   ├── assets/           # CSS, JS, images
│   ├── index.php         # Admin panel entry point
│   ├── storefront.php    # Public storefront entry point
│   └── api.php           # API entry point
├── storage/              # File storage
│   ├── uploads/          # Uploaded files
│   └── logs/             # Application logs
├── tests/                # Test files
├── database.sql          # Database schema and seed data
└── README.md            # This file
```

## Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Password Hashing**: BCrypt password hashing
- **SQL Injection Prevention**: PDO prepared statements only
- **XSS Protection**: Output escaping
- **Input Validation**: Server-side validation for all inputs
- **File Upload Validation**: Type and size validation
- **Session Security**: HTTP-only cookies, secure headers

## Deployment Guide

### Production Checklist

1. **Update Environment**
   ```env
   APP_ENV=production
   APP_URL=https://your-domain.com
   ```

2. **Database Optimization**
   - Enable query caching
   - Set up regular backups
   - Optimize tables regularly

3. **Security Hardening**
   - Enable HTTPS
   - Set secure session cookies
   - Disable error display
   - Enable error logging
   - Set appropriate file permissions

4. **Performance**
   - Enable OPcache
   - Configure Gzip compression
   - Set up CDN for assets
   - Enable browser caching

5. **Monitoring**
   - Set up error logging
   - Monitor disk space (uploads folder)
   - Monitor database size
   - Set up uptime monitoring

### Apache Production Configuration

```apache
<VirtualHost *:443>
    ServerName splashmarket.com
    DocumentRoot /var/www/splashmarket/public

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem

    <Directory /var/www/splashmarket/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    <Directory /var/www/splashmarket/storage>
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashmarket-error.log
    CustomLog ${APACHE_LOG_DIR}/splashmarket-access.log combined
</VirtualHost>
```

## Customization

### Adding New Payment Gateways

Extend the `OrderController` and `StorefrontController` to integrate real payment gateways:

1. Add payment method in checkout form
2. Create payment processor class
3. Handle payment callback/webhook
4. Update order status based on payment result

### Extending the Platform

The MVC architecture makes it easy to add new features:

1. Create new model in `app/models/`
2. Create corresponding controller in `app/controllers/`
3. Add routes in `config/routes.php`
4. Create views in `app/views/`

## Troubleshooting

### Database Connection Error
- Check database credentials in `.env`
- Ensure MySQL service is running
- Verify database exists

### 404 on All Routes
- Enable `mod_rewrite` (Apache)
- Check `.htaccess` file exists in public/
- Verify AllowOverride is set to All

### File Upload Errors
- Check `storage/uploads/` permissions (755)
- Verify PHP upload settings (upload_max_filesize, post_max_size)
- Check disk space

### Session Issues
- Ensure session directory is writable
- Check session configuration in php.ini

## License

This project is provided as-is for educational and commercial use.

## Support

For issues and questions:
1. Check the documentation above
2. Review the code comments
3. Check the functional tests for examples

## Credits

Built with vanilla PHP, MySQL, JavaScript, HTML, and CSS.

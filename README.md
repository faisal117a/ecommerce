# Cur1 Fashion - Full Stack Ecommerce Application

A complete ecommerce platform built with PHP 8, MySQL, and Bootstrap 5. Features an admin dashboard for product/category management and a minimalistic Shopify-inspired frontend design.

## Features

### Frontend
- Product catalog with category filtering
- Product detail pages
- Shopping cart functionality
- User authentication (login/register)
- Checkout process with Cash on Delivery
- Order history and tracking
- Responsive, minimalistic design

### Admin Dashboard
- Dashboard with statistics overview
- Product management (CRUD operations)
- Category management
- Order management with status updates
- Image upload functionality
- Clean, WordPress-like interface

## Installation

### Prerequisites
- XAMPP (or any PHP/MySQL environment)
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone/Download the project** to your XAMPP htdocs directory:
   ```
   D:\xampp\htdocs\cur1
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file
   - Or create a new database named `cur1_ecommerce` and run the SQL commands from `database.sql`

3. **Configure database connection** (if needed):
   - Edit `config/database.php`
   - Update database credentials if different from default:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'cur1_ecommerce');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Set up file permissions**:
   - Create the uploads directory: `assets/uploads/`
   - Ensure PHP has write permissions to this directory

5. **Access the application**:
   - Frontend: http://localhost/cur1/
   - Admin Login: http://localhost/cur1/admin/login.php
     - Default admin credentials:
       - Email: `admin@cur1.com`
       - Password: `admin123`
       - **Change this password immediately after first login!**

## Default Admin Account

- **Email:** admin@cur1.com
- **Password:** admin123

**⚠️ IMPORTANT:** Change the admin password immediately after first login for security.

## Project Structure

```
cur1/
├── admin/              # Admin dashboard
│   ├── dashboard.php
│   ├── products/       # Product management
│   ├── categories/     # Category management
│   ├── orders/         # Order management
│   └── login.php
├── auth/               # Authentication pages
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/             # Configuration files
│   ├── database.php
│   ├── auth.php
│   └── functions.php
├── includes/           # Reusable components
│   ├── admin_header.php
│   ├── admin_sidebar.php
│   └── admin_navbar.php
├── orders/             # Customer order pages
│   ├── checkout.php
│   ├── my-orders.php
│   └── view.php
├── assets/             # Static assets
│   ├── css/
│   │   ├── admin.css
│   │   └── shop.css
│   ├── js/
│   │   └── admin.js
│   └── uploads/       # Product images
├── index.php           # Shop homepage
├── product.php         # Product detail page
├── cart.php            # Shopping cart
├── database.sql        # Database schema
└── .htaccess           # Security configuration
```

## Usage

### For Customers
1. Browse products on the homepage
2. Filter by category
3. View product details
4. Add products to cart
5. Register/Login to place orders
6. Complete checkout with shipping information
7. View order history

### For Administrators
1. Login at `/admin/login.php`
2. Access dashboard for overview
3. Manage products: Add, edit, delete products with images
4. Manage categories: Create and organize product categories
5. Manage orders: View orders and update status (Pending → Processing → Shipped → Delivered)

## Payment Method

Currently supports **Cash on Delivery (COD)** only. The system is structured to easily add payment gateways (PayPal, Stripe, etc.) in the future.

## Security Features

- Password hashing (bcrypt)
- SQL injection prevention (PDO prepared statements)
- XSS protection (input sanitization)
- CSRF protection (ready for implementation)
- Secure file uploads (type validation)
- Session-based authentication
- Protected admin routes

## Technologies Used

- **Backend:** PHP 8+
- **Database:** MySQL/MariaDB
- **Frontend:** Bootstrap 5, Custom CSS
- **Authentication:** Session-based with password hashing
- **File Uploads:** Local storage

## Future Enhancements

- Payment gateway integration (PayPal, Stripe)
- Email notifications
- Product reviews and ratings
- Wishlist functionality
- Advanced search and filters
- Inventory management
- Coupon/discount system
- Multi-language support

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code comments or refer to the documentation in each file.


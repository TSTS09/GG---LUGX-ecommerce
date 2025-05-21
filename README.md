# GG-LUGX Gaming E-commerce Platform

<div align="center">
  <img src="Images/logo.png" alt="GG-LUGX Logo" width="200"/>
  <h3>Premium Gaming Store</h3>
  <p>An advanced e-commerce platform for gamers with modern UI, secure payment processing, and comprehensive admin tools</p>
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-purple.svg)](https://getbootstrap.com/)
  [![Live Demo](https://img.shields.io/badge/Live-Demo-brightgreen.svg)](https://gg-lugx.infy.uk/GGLUGX/index.php?i=1)
</div>

## 📌 Table of Contents
- [Overview](#-overview)
- [Key Features](#-key-features)
- [Demo & Screenshots](#-demo--screenshots)
- [Access Credentials](#-access-credentials)
- [Technology Stack](#-technology-stack)
- [Installation & Setup](#-installation--setup)
- [Database Structure](#-database-structure)
- [Payment Integration](#-payment-integration)
- [Project Structure](#-project-structure)
- [Admin Dashboard Guide](#-admin-dashboard-guide)
- [Contributing](#-contributing)
- [License](#-license)
- [Credits](#-credits)

## 🚀 Overview

GG-LUGX is a comprehensive, feature-rich e-commerce platform designed for gaming enthusiasts. The system provides an intuitive shopping experience for customers while offering powerful management capabilities for administrators. From browsing and purchasing games to tracking orders and managing wishlists, GG-LUGX delivers a complete online shopping solution for the gaming industry.

**Live Site:** [https://gg-lugx.infy.uk/GGLUGX/index.php?i=1](https://gg-lugx.infy.uk/GGLUGX/index.php?i=1)

## ✨ Key Features

### For Customers
- **Intuitive Product Browsing:** Explore games by categories, brands, or through search
- **User Account Management:** Register, login, update profile, and view purchase history
- **Shopping Cart System:** Add, remove items, and adjust quantities
- **Wishlist Functionality:** Save favorite products for future purchase
- **Guest Checkout:** Purchase items without registration
- **Order Tracking:** Monitor the status of orders from processing to delivery
- **Game Bundles:** Purchase curated collections of games at discounted prices
- **Secure Payments:** Integrated with PayStack for safe transactions
- **Mobile-Responsive Design:** Shop seamlessly across devices

### For Administrators
- **Comprehensive Dashboard:** Overview of sales, products, and customer activities
- **Product Management:** Add, edit, delete, and organize products
- **Category & Brand Control:** Create and manage product classifications
- **Order Processing:** View, update status, and manage customer orders
- **Bundle Creation:** Create special product collections with custom pricing
- **User Management:** Review and manage customer accounts
- **Wishlist Analytics:** Gain insights into popular products and customer preferences
- **Sales Reporting:** Generate detailed reports on transactions and product performance

## 📸 Demo & Screenshots

<div align="center">
  <em>Visit the live site to explore the full functionality!</em>
</div>

## 🔑 Access Credentials

### Admin Login
- **URL:** [https://gg-lugx.infy.uk/GGLUGX/Login/login.php](https://gg-lugx.infy.uk/GGLUGX/Login/login.php)
- **Email:** huza@mailinator.com
- **Password:** Pa$$w0rd!

### Customer Login
- **URL:** [https://gg-lugx.infy.uk/GGLUGX/Login/login.php](https://gg-lugx.infy.uk/GGLUGX/Login/login.php)
- **Email:** namyze@mailinator.com
- **Password:** Pa$$w0rd!

## 💻 Technology Stack

- **Frontend:**
  - HTML5 & CSS3
  - JavaScript & jQuery
  - Bootstrap 5
  - Chart.js (for admin analytics)

- **Backend:**
  - PHP 8.1
  - MySQL Database
  - MVC Architecture
  - PayStack API Integration
  - PHPMailer

- **Security Features:**
  - Password hashing with bcrypt
  - Prepared statements for SQL queries
  - Session management
  - Input validation and sanitization
  - CSRF protection

## 🛠 Installation & Setup

### Prerequisites
- Web server (Apache/Nginx)
- PHP 8.1+ with necessary extensions
- MySQL 5.7 or higher
- Composer (recommended for dependencies)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/gg-lugx.git
   cd gg-lugx
   ```

2. **Database Setup**
   - Create a MySQL database named `Ecommerce`
   - Import the SQL file from the project root:
   ```bash
   mysql -u username -p Ecommerce < Ecommerce.sql
   ```

3. **Configure Database Connection**
   - Navigate to `Setting/db_cred.php`
   - Update the database connection parameters:
   ```php
   define('SERVER', 'localhost');
   define('USERNAME', 'your_username');
   define('PASSWORD', 'your_password');
   define('DATABASE', 'Ecommerce');
   ```

4. **Configure Payment Integration**
   - Update PayStack API keys in `View/payment.php`
   - For testing, you can use the provided test keys or obtain your own from [PayStack](https://paystack.com/)

5. **Email Configuration (Optional)**
   - Update SMTP settings in `Actions/process_contact.php` 
   - Replace with your email service provider details

6. **File Permissions**
   - Ensure `Images/product/` directory is writable
   ```bash
   chmod -R 755 Images/product/
   ```

7. **Web Server Configuration**
   - Point your web server to the project root directory
   - Set up appropriate URL rewriting rules if necessary

## 📊 Database Structure

The database consists of several key tables:

- **products**: Stores all product information
- **categories**: Product categories
- **brands**: Product brands
- **customer**: User account information
- **cart**: Shopping cart items
- **wishlist**: Saved items
- **orders**: Order information
- **orderdetails**: Items within each order
- **payment**: Payment transaction details
- **bundle_items**: Products included in bundles

For a complete view of the database schema, refer to the `Ecommerce.sql` file.

## 💳 Payment Integration

GG-LUGX uses PayStack for payment processing, offering:

- Secure credit/debit card payments
- Currency conversion (USD to GHS)
- Transaction verification
- Automated receipts and confirmations

To test payments on the live site, use PayStack's test cards:
- Card Number: 4084 0840 8408 4081
- Expiry: Any future date
- CVV: Any 3 digits
- PIN: Any 4 digits
- OTP: Use 123456

## 📁 Project Structure

```
GG-LUGX/
├── Actions/            # Form processing scripts
├── Admin/              # Admin panel interfaces
├── Classes/            # Core data classes
├── Controllers/        # Application logic
├── CSS/                # Stylesheet files
├── Images/             # Site images & product photos
│   └── product/        # Product images storage
├── includes/           # Reusable page components
├── JS/                 # JavaScript files
├── Login/              # Authentication pages
├── Setting/            # Configuration files
├── View/               # Customer-facing pages
├── Ecommerce.sql       # Database schema & initial data
└── index.php           # Application entry point
```

## 📋 Admin Dashboard Guide

The admin dashboard provides comprehensive tools for store management:

1. **Products Management**
   - Create, edit, and delete products
   - Manage product images, descriptions, and pricing
   - Categorize products by brand and type

2. **Order Processing**
   - View all orders with filtering options
   - Update order status (Pending, Processing, Shipped, Delivered, etc.)
   - Generate invoices and export order data

3. **Bundle Management**
   - Create special game bundles
   - Set discounted pricing
   - Include multiple products in a single bundle

4. **Wishlist Analytics**
   - Track most wishlisted products
   - Analyze customer preferences
   - Identify trending products

5. **User Management**
   - View customer accounts
   - Monitor purchasing patterns
   - Manage account status

## 🤝 Contributing

We welcome contributions to enhance GG-LUGX! Here's how you can contribute:

1. Fork the repository
2. Create a feature branch
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. Commit your changes
   ```bash
   git commit -m 'Add some amazing feature'
   ```
4. Push to the branch
   ```bash
   git push origin feature/amazing-feature
   ```
5. Open a Pull Request

### Contribution Guidelines
- Ensure code adheres to the existing style
- Update documentation for any new features
- Write clean, maintainable, and testable code
- Add appropriate comments for complex logic

## 📜 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👏 Credits

- **Developer Team:** [Your Team/Company Name]
- **UI Design:** Based on LUGX Gaming Template
- **Icons:** Font Awesome
- **Bootstrap Components:** Bootstrap 5
- **Special Thanks:** To all beta testers and contributors

---

<div align="center">
  <p>© 2025 GG-LUGX Gaming. All Rights Reserved.</p>
  <p>
    <a href="mailto:support@gg-lugx.infy.uk">Contact Support</a> •
    <a href="https://gg-lugx.infy.uk/GGLUGX/View/contact.php">Feedback</a>
  </p>
</div>

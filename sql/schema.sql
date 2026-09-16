-- Rojam Boutique Online Clothing Management System
-- MySQL schema for XAMPP / phpMyAdmin
-- Import this first, then open setup.php once in the browser to create the admin account.

CREATE DATABASE IF NOT EXISTS rojam_boutique
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE rojam_boutique;

-- Drop in dependency order so re-import is clean
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS admins;

-- Admins: separate from customers (system operators)
CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers: shoppers
CREATE TABLE customers (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories: product classification (drives the category dropdown)
CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(80) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products: reference categories by foreign key
CREATE TABLE products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(120) NOT NULL,
  category_id INT NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  stock_quantity INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

CREATE TABLE orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  order_status ENUM('Pending','Processing','Ready for Pickup','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_customer
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

CREATE TABLE order_details (
  order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10, 2) NOT NULL,
  subtotal DECIMAL(10, 2) NOT NULL,
  CONSTRAINT fk_order_details_order
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_order_details_product
    FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- Seed categories
INSERT INTO categories (category_name) VALUES
('Dresses'), ('Jackets'), ('Tops'), ('Bottoms'), ('Shoes');

-- Seed products (category_id maps to the order above: 1=Dresses ... 5=Shoes)
INSERT INTO products (product_name, category_id, description, price, stock_quantity) VALUES
('Elegant Floral Dress', 1, 'Soft floral dress suitable for casual and semi-formal wear.', 3500.00, 12),
('Weekend Maxi Dress',   1, 'Flowing maxi dress ideal for weekend outings.',              3900.00, 6),
('Classic Denim Jacket', 2, 'Stylish denim jacket for layering and everyday wear.',       4200.00, 8),
('Knitted Cardigan',     2, 'Warm cardigan in a neutral tone.',                           3000.00, 9),
('Office Blouse',        3, 'Comfortable blouse for work and smart casual outfits.',      1800.00, 15),
('High Waist Trousers',  4, 'Smart high-waist trousers with a clean tailored finish.',    2500.00, 10),
('Leather Ankle Boots',  5, 'Durable leather ankle boots for all-day wear.',              5500.00, 5);

-- Reference sales report query (used by the reports page)
-- SELECT p.product_name, SUM(od.quantity) AS quantity_sold, SUM(od.subtotal) AS revenue
-- FROM order_details od
-- JOIN products p ON p.product_id = od.product_id
-- JOIN orders o   ON o.order_id   = od.order_id
-- WHERE o.order_status <> 'Cancelled'
-- GROUP BY p.product_id, p.product_name
-- ORDER BY revenue DESC;

<?php
// Run this once to create the database and tables
$conn = new mysqli("localhost", "root", "");

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS pos_db");
$conn->select_db("pos_db");

// Orders table
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    cash_tendered DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Order items table
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    emoji VARCHAR(10) DEFAULT '',
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)");

echo "✅ Database 'pos_db' and tables created successfully!";
$conn->close();
?>

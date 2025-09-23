<?php
require_once 'database.php';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS balut_business");
    $pdo->exec("USE balut_business");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'vendor') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create duck_batches table
    $pdo->exec("CREATE TABLE IF NOT EXISTS duck_batches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        batch_name VARCHAR(100) NOT NULL,
        type ENUM('layering', 'future') NOT NULL,
        count INT DEFAULT 0,
        male_count INT DEFAULT 0,
        female_count INT DEFAULT 0,
        status ENUM('active', 'inactive', 'future') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create sales table
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_name VARCHAR(100) NOT NULL,
        item_sold VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        sale_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create egg_batches table
    $pdo->exec("CREATE TABLE IF NOT EXISTS egg_batches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        batch_name VARCHAR(100) NOT NULL,
        egg_count INT NOT NULL,
        incubation_start DATE NOT NULL,
        status ENUM('in_progress', 'completed', 'failed') DEFAULT 'in_progress',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Insert default admin user (password: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$admin_password', 'admin')");
    
    // Insert default vendor user (password: vendor123)
    $vendor_password = password_hash('vendor123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, role) VALUES ('vendor', '$vendor_password', 'vendor')");
    
    // Insert sample duck batches
    $pdo->exec("INSERT IGNORE INTO duck_batches (id, batch_name, type, count, status) VALUES (1, 'Batch A', 'layering', 120, 'active')");
    $pdo->exec("INSERT IGNORE INTO duck_batches (id, batch_name, type, male_count, female_count, status) VALUES (2, 'Batch B', 'future', 60, 80, 'future')");
    
    // Insert sample egg batch
    $pdo->exec("INSERT IGNORE INTO egg_batches (id, batch_name, egg_count, incubation_start, status) VALUES (1, 'Egg Batch A', 200, '2025-01-20', 'in_progress')");
    
    // Insert sample sales data
    $pdo->exec("INSERT IGNORE INTO sales (vendor_name, item_sold, amount, sale_date) VALUES 
        ('Juan Dela Cruz', '50 balut eggs', 250.00, CURDATE()),
        ('Maria Santos', '100 balut eggs', 500.00, CURDATE() - INTERVAL 1 DAY),
        ('Pedro Garcia', '75 balut eggs', 375.00, CURDATE() - INTERVAL 2 DAY)");
    
    echo "Database initialized successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
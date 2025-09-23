# Balut Business Management System

A comprehensive PHP and MySQL-based system for managing a balut business, including duck management, feed calculation, sales tracking, and incubator monitoring.

## Features

### 🔐 User Authentication
- Admin and Vendor role-based access
- Secure login system with password hashing
- Session management

### 🦆 Duck Management
- Track layering ducks (active egg producers)
- Manage future ducks with male/female counts
- Add, edit, and delete duck batches
- Status tracking (active, inactive, future)

### 🥬 Feed Calculator
- Calculate daily, weekly, and monthly feed requirements
- Support for different feed types (starter, layer, booster)
- Vitamin requirement calculations
- Real-time calculations based on duck count

### 💰 Sales Recording
- Record sales transactions from vendors and staff
- View recent sales history
- Interactive sales analytics with charts
- Multiple time period views (daily, weekly, monthly, yearly)

### 🥚 Incubator Management
- Track egg batches and incubation progress
- Monitor incubation timeline (28-day cycle)
- Visual progress bars
- Status tracking (in progress, completed, failed)
- Incubation tips and guidelines

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone or download the project files**

2. **Database Setup**
   ```bash
   # Navigate to the project directory
   cd balut-business-system
   
   # Run the database initialization script
   php config/init_db.php
   ```

3. **Configure Database Connection**
   - Edit `config/database.php` if needed
   - Update database credentials (host, username, password)

4. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure PHP is properly configured

5. **Access the System**
   - Open your browser and navigate to the project URL
   - You'll be redirected to the login page

## Default Login Credentials

### Admin Account
- **Username:** admin
- **Password:** admin123

### Vendor Account
- **Username:** vendor  
- **Password:** vendor123

## Database Schema

### Tables
- `users` - User accounts and authentication
- `duck_batches` - Duck batch management
- `sales` - Sales transaction records
- `egg_batches` - Incubator egg batch tracking

## File Structure

```
balut-business-system/
├── config/
│   ├── database.php          # Database connection class
│   └── init_db.php          # Database initialization script
├── includes/
│   └── auth.php             # Authentication functions
├── api/
│   └── sales-data.php       # Sales data API endpoint
├── index.php                # Dashboard
├── login.php                # Login page
├── logout.php               # Logout handler
├── manage-ducks.php         # Duck management
├── feed-calculator.php      # Feed calculator
├── record-sales.php         # Sales recording
├── incubator.php           # Incubator management
└── README.md               # This file
```

## Usage

### Dashboard
- Overview of all system modules
- Quick access to main features
- User information display

### Duck Management
- Add new duck batches (layering or future)
- Edit existing batches
- Track duck counts and status
- Separate management for layering and future ducks

### Feed Calculator
- Enter duck count and feed type
- Get instant calculations for daily, weekly, and monthly requirements
- Includes both feed and vitamin calculations

### Sales Recording
- Record new sales transactions
- View recent sales history
- Analyze sales data with interactive charts
- Multiple time period analytics

### Incubator Management
- Add new egg batches
- Track incubation progress with visual indicators
- Update batch status
- View incubation tips and guidelines

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- Role-based access control
- Input validation and sanitization

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile devices
- Bootstrap 5 for consistent UI

## Support

For issues or questions, please check the code comments or create an issue in your project repository.

## License

This project is open source and available under the MIT License.
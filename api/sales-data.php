<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$period = $_GET['period'] ?? 'daily';

function getSalesData($db, $period) {
    switch ($period) {
        case 'daily':
            $query = "SELECT DATE(sale_date) as period, SUM(amount) as total 
                     FROM sales 
                     WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     GROUP BY DATE(sale_date) 
                     ORDER BY period";
            break;
        case 'weekly':
            $query = "SELECT CONCAT('Week ', WEEK(sale_date)) as period, SUM(amount) as total 
                     FROM sales 
                     WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
                     GROUP BY WEEK(sale_date) 
                     ORDER BY WEEK(sale_date)";
            break;
        case 'monthly':
            $query = "SELECT DATE_FORMAT(sale_date, '%M %Y') as period, SUM(amount) as total 
                     FROM sales 
                     WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(sale_date, '%Y-%m') 
                     ORDER BY DATE_FORMAT(sale_date, '%Y-%m')";
            break;
        case 'yearly':
            $query = "SELECT YEAR(sale_date) as period, SUM(amount) as total 
                     FROM sales 
                     GROUP BY YEAR(sale_date) 
                     ORDER BY period";
            break;
        default:
            return [];
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$data = getSalesData($db, $period);
echo json_encode($data);
?>
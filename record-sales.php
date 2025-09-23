<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_sale') {
    $vendor_name = $_POST['vendorName'];
    $item_sold = $_POST['itemSold'];
    $amount = $_POST['saleAmount'];
    $sale_date = $_POST['saleDate'] ?? date('Y-m-d');
    
    if ($vendor_name && $item_sold && $amount) {
        $query = "INSERT INTO sales (vendor_name, item_sold, amount, sale_date) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$vendor_name, $item_sold, $amount, $sale_date])) {
            $message = '<div class="alert alert-success">Sale recorded successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error recording sale.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Please fill in all fields.</div>';
    }
}

// Fetch sales data for charts
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
            $query = "SELECT YEARWEEK(sale_date) as period, SUM(amount) as total 
                     FROM sales 
                     WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
                     GROUP BY YEARWEEK(sale_date) 
                     ORDER BY period";
            break;
        case 'monthly':
            $query = "SELECT DATE_FORMAT(sale_date, '%Y-%m') as period, SUM(amount) as total 
                     FROM sales 
                     WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(sale_date, '%Y-%m') 
                     ORDER BY period";
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

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Record Sales</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
      <a class="navbar-brand" href="index.php">Balut Business</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="manage-ducks.php">Manage Ducks</a></li>
          <li class="nav-item"><a class="nav-link" href="feed-calculator.php">Feed Calculator</a></li>
          <li class="nav-item"><a class="nav-link active" href="record-sales.php">Record Sales</a></li>
          <li class="nav-item"><a class="nav-link" href="incubator.php">Incubator</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <?php echo htmlspecialchars($user['username']); ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="bg-light p-4 text-center">
    <h2>Record Sales</h2>
    <p class="lead">Log vendor and staff sales transactions</p>
  </header>

  <main class="container my-5">
    <?php echo $message; ?>
    
    <!-- Sales Entry Form -->
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Sales Entry</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="add_sale">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="vendorName" class="form-label">Vendor/Staff Name</label>
              <input type="text" class="form-control" id="vendorName" name="vendorName" placeholder="Enter name" required>
            </div>
            <div class="col-md-6">
              <label for="itemSold" class="form-label">Item Sold</label>
              <input type="text" class="form-control" id="itemSold" name="itemSold" placeholder="e.g., 50 balut eggs" required>
            </div>
            <div class="col-md-4">
              <label for="saleAmount" class="form-label">Amount (₱)</label>
              <input type="number" step="0.01" class="form-control" id="saleAmount" name="saleAmount" placeholder="Enter amount" required>
            </div>
            <div class="col-md-4">
              <label for="saleDate" class="form-label">Sale Date</label>
              <input type="date" class="form-control" id="saleDate" name="saleDate" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-success w-100">Submit Sale</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Recent Sales -->
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Recent Sales</div>
      <div class="card-body">
        <?php
        $recent_query = "SELECT * FROM sales ORDER BY created_at DESC LIMIT 10";
        $recent_stmt = $db->prepare($recent_query);
        $recent_stmt->execute();
        $recent_sales = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Date</th>
                <th>Vendor/Staff</th>
                <th>Item</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_sales as $sale): ?>
              <tr>
                <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                <td><?php echo htmlspecialchars($sale['vendor_name']); ?></td>
                <td><?php echo htmlspecialchars($sale['item_sold']); ?></td>
                <td>₱<?php echo number_format($sale['amount'], 2); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Sales Graphs -->
    <div class="card">
      <div class="card-header bg-success text-white">Sales Analytics</div>
      <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="salesGraphTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab">Daily</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab">Weekly</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab">Monthly</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="yearly-tab" data-bs-toggle="tab" data-bs-target="#yearly" type="button" role="tab">Yearly</button>
          </li>
        </ul>

        <div class="tab-content" id="salesGraphContent">
          <div class="tab-pane fade show active" id="daily" role="tabpanel">
            <canvas id="dailyChart" width="600" height="300"></canvas>
          </div>
          <div class="tab-pane fade" id="weekly" role="tabpanel">
            <canvas id="weeklyChart" width="600" height="300"></canvas>
          </div>
          <div class="tab-pane fade" id="monthly" role="tabpanel">
            <canvas id="monthlyChart" width="600" height="300"></canvas>
          </div>
          <div class="tab-pane fade" id="yearly" role="tabpanel">
            <canvas id="yearlyChart" width="600" height="300"></canvas>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    const chartConfig = (labels, data, label) => ({
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: label,
          data: data,
          backgroundColor: 'rgba(25,135,84,0.6)',
          borderColor: 'rgba(25,135,84,1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    const charts = {};

    function renderChart(id, labels, data, label) {
      if (!charts[id]) {
        const ctx = document.getElementById(id);
        charts[id] = new Chart(ctx, chartConfig(labels, data, label));
      }
    }

    // Get real data from PHP
    const salesData = {
      daily: <?php echo json_encode(getSalesData($db, 'daily')); ?>,
      weekly: <?php echo json_encode(getSalesData($db, 'weekly')); ?>,
      monthly: <?php echo json_encode(getSalesData($db, 'monthly')); ?>,
      yearly: <?php echo json_encode(getSalesData($db, 'yearly')); ?>
    };

    // Render daily chart immediately
    const dailyLabels = salesData.daily.map(item => item.period);
    const dailyData = salesData.daily.map(item => parseFloat(item.total));
    renderChart('dailyChart', dailyLabels, dailyData, 'Daily Sales (₱)');

    // Render others on tab activation
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
      tab.addEventListener('shown.bs.tab', function (e) {
        const targetId = e.target.getAttribute('data-bs-target');
        switch (targetId) {
          case '#weekly':
            const weeklyLabels = salesData.weekly.map(item => `Week ${item.period}`);
            const weeklyData = salesData.weekly.map(item => parseFloat(item.total));
            renderChart('weeklyChart', weeklyLabels, weeklyData, 'Weekly Sales (₱)');
            break;
          case '#monthly':
            const monthlyLabels = salesData.monthly.map(item => item.period);
            const monthlyData = salesData.monthly.map(item => parseFloat(item.total));
            renderChart('monthlyChart', monthlyLabels, monthlyData, 'Monthly Sales (₱)');
            break;
          case '#yearly':
            const yearlyLabels = salesData.yearly.map(item => item.period);
            const yearlyData = salesData.yearly.map(item => parseFloat(item.total));
            renderChart('yearlyChart', yearlyLabels, yearlyData, 'Yearly Sales (₱)');
            break;
        }
      });
    });
  </script>
</body>
</html>
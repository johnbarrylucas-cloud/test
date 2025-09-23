<?php
require_once 'includes/auth.php';
requireLogin();

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duckCount = intval($_POST['duckCount'] ?? 0);
    $feedType = $_POST['feedType'] ?? 'starter';
    
    if ($duckCount > 0) {
        // Feed calculation logic (grams per duck per day)
        $feedRates = [
            'starter' => 150,  // 150g per duck per day
            'layer' => 120,    // 120g per duck per day
            'booster' => 180   // 180g per duck per day
        ];
        
        $dailyFeedGrams = $duckCount * $feedRates[$feedType];
        $dailyFeedKg = $dailyFeedGrams / 1000;
        $weeklyFeedKg = $dailyFeedKg * 7;
        $monthlyFeedKg = $dailyFeedKg * 30;
        
        // Vitamin calculation (assuming 2g per duck per day)
        $dailyVitaminGrams = $duckCount * 2;
        $weeklyVitaminGrams = $dailyVitaminGrams * 7;
        $monthlyVitaminGrams = $dailyVitaminGrams * 30;
        
        $result = [
            'duckCount' => $duckCount,
            'feedType' => $feedType,
            'dailyFeedKg' => $dailyFeedKg,
            'weeklyFeedKg' => $weeklyFeedKg,
            'monthlyFeedKg' => $monthlyFeedKg,
            'dailyVitaminGrams' => $dailyVitaminGrams,
            'weeklyVitaminGrams' => $weeklyVitaminGrams,
            'monthlyVitaminGrams' => $monthlyVitaminGrams
        ];
    } else {
        $error = "Please enter a valid number of ducks.";
    }
}

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Feed Calculator</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
          <li class="nav-item"><a class="nav-link active" href="feed-calculator.php">Feed Calculator</a></li>
          <li class="nav-item"><a class="nav-link" href="record-sales.php">Record Sales</a></li>
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
    <h2>🥬 Feed & Vitamin Calculator</h2>
    <p class="lead">Estimate daily feed requirements based on duck count</p>
  </header>

  <main class="container my-5">
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-success text-white">Calculator</div>
          <div class="card-body">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
              <div class="row g-3">
                <div class="col-md-12">
                  <label for="duckCount" class="form-label">Number of Ducks</label>
                  <input type="number" class="form-control" id="duckCount" name="duckCount" 
                         placeholder="Enter count" value="<?php echo $_POST['duckCount'] ?? ''; ?>" required>
                </div>
                <div class="col-md-12">
                  <label for="feedType" class="form-label">Feed Type</label>
                  <select class="form-select" id="feedType" name="feedType">
                    <option value="starter" <?php echo ($_POST['feedType'] ?? '') === 'starter' ? 'selected' : ''; ?>>Starter (150g/duck/day)</option>
                    <option value="layer" <?php echo ($_POST['feedType'] ?? '') === 'layer' ? 'selected' : ''; ?>>Layer (120g/duck/day)</option>
                    <option value="booster" <?php echo ($_POST['feedType'] ?? '') === 'booster' ? 'selected' : ''; ?>>Booster (180g/duck/day)</option>
                  </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                  <button type="submit" class="btn btn-success">Calculate</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <?php if ($result): ?>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-info text-white">Calculation Results</div>
          <div class="card-body">
            <h5>Feed Requirements for <?php echo $result['duckCount']; ?> Ducks (<?php echo ucfirst($result['feedType']); ?>)</h5>
            
            <div class="row g-3 mt-2">
              <div class="col-12">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title">📅 Daily Requirements</h6>
                    <p class="mb-1"><strong>Feed:</strong> <?php echo number_format($result['dailyFeedKg'], 2); ?> kg</p>
                    <p class="mb-0"><strong>Vitamins:</strong> <?php echo $result['dailyVitaminGrams']; ?> grams</p>
                  </div>
                </div>
              </div>
              
              <div class="col-12">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title">📊 Weekly Requirements</h6>
                    <p class="mb-1"><strong>Feed:</strong> <?php echo number_format($result['weeklyFeedKg'], 2); ?> kg</p>
                    <p class="mb-0"><strong>Vitamins:</strong> <?php echo $result['weeklyVitaminGrams']; ?> grams</p>
                  </div>
                </div>
              </div>
              
              <div class="col-12">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title">📈 Monthly Requirements</h6>
                    <p class="mb-1"><strong>Feed:</strong> <?php echo number_format($result['monthlyFeedKg'], 2); ?> kg</p>
                    <p class="mb-0"><strong>Vitamins:</strong> <?php echo number_format($result['monthlyVitaminGrams'] / 1000, 2); ?> kg</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning rounded">
              <small><strong>Note:</strong> These are estimated requirements. Actual consumption may vary based on duck age, weather conditions, and activity level.</small>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
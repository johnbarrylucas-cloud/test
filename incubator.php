<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_batch':
                $batch_name = $_POST['batch_name'];
                $egg_count = $_POST['egg_count'];
                $incubation_start = $_POST['incubation_start'];
                
                $query = "INSERT INTO egg_batches (batch_name, egg_count, incubation_start, status) VALUES (?, ?, ?, 'in_progress')";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$batch_name, $egg_count, $incubation_start])) {
                    $message = '<div class="alert alert-success">Egg batch added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error adding egg batch.</div>';
                }
                break;
                
            case 'update_batch':
                $id = $_POST['id'];
                $batch_name = $_POST['batch_name'];
                $egg_count = $_POST['egg_count'];
                $incubation_start = $_POST['incubation_start'];
                $status = $_POST['status'];
                
                $query = "UPDATE egg_batches SET batch_name = ?, egg_count = ?, incubation_start = ?, status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$batch_name, $egg_count, $incubation_start, $status, $id])) {
                    $message = '<div class="alert alert-success">Egg batch updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error updating egg batch.</div>';
                }
                break;
                
            case 'delete_batch':
                $id = $_POST['id'];
                $query = "DELETE FROM egg_batches WHERE id = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$id])) {
                    $message = '<div class="alert alert-success">Egg batch deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error deleting egg batch.</div>';
                }
                break;
        }
        header('Location: incubator.php');
        exit();
    }
}

// Fetch egg batches
$query = "SELECT *, DATEDIFF(CURDATE(), incubation_start) as days_incubated FROM egg_batches ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$egg_batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Incubator Dashboard</title>
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
          <li class="nav-item"><a class="nav-link" href="feed-calculator.php">Feed Calculator</a></li>
          <li class="nav-item"><a class="nav-link" href="record-sales.php">Record Sales</a></li>
          <li class="nav-item"><a class="nav-link active" href="incubator.php">Incubator</a></li>
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
    <h2>🧪 Incubator Dashboard</h2>
    <p class="lead">Track egg batches and incubation progress</p>
  </header>

  <main class="container my-5">
    <?php echo $message; ?>
    
    <!-- Add New Egg Batch -->
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Add New Egg Batch</div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <input type="hidden" name="action" value="add_batch">
          <div class="col-md-4">
            <label for="batch_name" class="form-label">Batch Name</label>
            <input type="text" class="form-control" name="batch_name" required>
          </div>
          <div class="col-md-3">
            <label for="egg_count" class="form-label">Egg Count</label>
            <input type="number" class="form-control" name="egg_count" required>
          </div>
          <div class="col-md-3">
            <label for="incubation_start" class="form-label">Incubation Start Date</label>
            <input type="date" class="form-control" name="incubation_start" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Add Batch</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Egg Batch Overview -->
    <div class="card">
      <div class="card-header bg-success text-white">Egg Batch Overview</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Batch Name</th>
                <th>Egg Count</th>
                <th>Incubation Start</th>
                <th>Days Incubated</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($egg_batches as $batch): ?>
              <?php
                $days = $batch['days_incubated'];
                $progress = min(($days / 28) * 100, 100); // 28 days incubation period
                $progress_class = $progress < 50 ? 'bg-warning' : ($progress < 100 ? 'bg-info' : 'bg-success');
              ?>
              <tr>
                <td><?php echo htmlspecialchars($batch['batch_name']); ?></td>
                <td><?php echo $batch['egg_count']; ?></td>
                <td><?php echo date('M d, Y', strtotime($batch['incubation_start'])); ?></td>
                <td><?php echo $days; ?> days</td>
                <td>
                  <span class="badge bg-<?php 
                    echo $batch['status'] === 'in_progress' ? 'primary' : 
                         ($batch['status'] === 'completed' ? 'success' : 'danger'); 
                  ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $batch['status'])); ?>
                  </span>
                </td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" 
                         style="width: <?php echo $progress; ?>%" 
                         aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                      <?php echo round($progress, 1); ?>%
                    </div>
                  </div>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $batch['id']; ?>">Update</button>
                  <button class="btn btn-sm btn-outline-danger" onclick="deleteBatch(<?php echo $batch['id']; ?>)">Delete</button>
                </td>
              </tr>
              
              <!-- Edit Modal -->
              <div class="modal fade" id="editModal<?php echo $batch['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Update Egg Batch</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                      <div class="modal-body">
                        <input type="hidden" name="action" value="update_batch">
                        <input type="hidden" name="id" value="<?php echo $batch['id']; ?>">
                        <div class="mb-3">
                          <label class="form-label">Batch Name</label>
                          <input type="text" class="form-control" name="batch_name" value="<?php echo htmlspecialchars($batch['batch_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Egg Count</label>
                          <input type="number" class="form-control" name="egg_count" value="<?php echo $batch['egg_count']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Incubation Start Date</label>
                          <input type="date" class="form-control" name="incubation_start" value="<?php echo $batch['incubation_start']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="status">
                            <option value="in_progress" <?php echo $batch['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $batch['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $batch['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                          </select>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <?php if (empty($egg_batches)): ?>
        <div class="text-center py-4">
          <p class="text-muted">No egg batches found. Add your first batch above.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Incubation Tips -->
    <div class="card mt-4">
      <div class="card-header bg-info text-white">Incubation Tips</div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>🌡️ Temperature Control</h6>
            <ul class="small">
              <li>Maintain 37.5°C (99.5°F) for duck eggs</li>
              <li>Check temperature daily</li>
              <li>Avoid sudden temperature changes</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>💧 Humidity Levels</h6>
            <ul class="small">
              <li>55-60% humidity for first 25 days</li>
              <li>65-70% humidity for last 3 days</li>
              <li>Monitor with reliable hygrometer</li>
            </ul>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-md-6">
            <h6>🔄 Turning Schedule</h6>
            <ul class="small">
              <li>Turn eggs 3-5 times daily</li>
              <li>Stop turning 3 days before hatching</li>
              <li>Mark eggs to track turning</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>📅 Timeline</h6>
            <ul class="small">
              <li>Duck eggs: 28 days incubation</li>
              <li>Candling: Day 7 and 14</li>
              <li>Lockdown: Day 25</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function deleteBatch(id) {
      if (confirm('Are you sure you want to delete this egg batch?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="action" value="delete_batch">
          <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
</body>
</html>
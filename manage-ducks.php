<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_layering':
                $batch_name = $_POST['batch_name'];
                $count = $_POST['count'];
                $query = "INSERT INTO duck_batches (batch_name, type, count, status) VALUES (?, 'layering', ?, 'active')";
                $stmt = $db->prepare($query);
                $stmt->execute([$batch_name, $count]);
                break;
                
            case 'add_future':
                $batch_name = $_POST['batch_name'];
                $male_count = $_POST['male_count'];
                $female_count = $_POST['female_count'];
                $query = "INSERT INTO duck_batches (batch_name, type, male_count, female_count, status) VALUES (?, 'future', ?, ?, 'future')";
                $stmt = $db->prepare($query);
                $stmt->execute([$batch_name, $male_count, $female_count]);
                break;
                
            case 'update_layering':
                $id = $_POST['id'];
                $batch_name = $_POST['batch_name'];
                $count = $_POST['count'];
                $status = $_POST['status'];
                $query = "UPDATE duck_batches SET batch_name = ?, count = ?, status = ? WHERE id = ? AND type = 'layering'";
                $stmt = $db->prepare($query);
                $stmt->execute([$batch_name, $count, $status, $id]);
                break;
                
            case 'update_future':
                $id = $_POST['id'];
                $batch_name = $_POST['batch_name'];
                $male_count = $_POST['male_count'];
                $female_count = $_POST['female_count'];
                $status = $_POST['status'];
                $query = "UPDATE duck_batches SET batch_name = ?, male_count = ?, female_count = ?, status = ? WHERE id = ? AND type = 'future'";
                $stmt = $db->prepare($query);
                $stmt->execute([$batch_name, $male_count, $female_count, $status, $id]);
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM duck_batches WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                break;
        }
        header('Location: manage-ducks.php');
        exit();
    }
}

// Fetch layering ducks
$layering_query = "SELECT * FROM duck_batches WHERE type = 'layering' ORDER BY created_at DESC";
$layering_stmt = $db->prepare($layering_query);
$layering_stmt->execute();
$layering_ducks = $layering_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch future ducks
$future_query = "SELECT * FROM duck_batches WHERE type = 'future' ORDER BY created_at DESC";
$future_stmt = $db->prepare($future_query);
$future_stmt->execute();
$future_ducks = $future_stmt->fetchAll(PDO::FETCH_ASSOC);

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Ducks</title>
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
          <li class="nav-item"><a class="nav-link active" href="manage-ducks.php">Manage Ducks</a></li>
          <li class="nav-item"><a class="nav-link" href="feed-calculator.php">Feed Calculator</a></li>
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
    <h2>Manage Ducks</h2>
    <p class="lead">Track and update current and future duck batches</p>
  </header>

  <main class="container my-5">
    <!-- Add New Layering Batch -->
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Add New Layering Batch</div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <input type="hidden" name="action" value="add_layering">
          <div class="col-md-6">
            <label for="batch_name" class="form-label">Batch Name</label>
            <input type="text" class="form-control" name="batch_name" required>
          </div>
          <div class="col-md-4">
            <label for="count" class="form-label">Count</label>
            <input type="number" class="form-control" name="count" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Add Batch</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Layering Ducks Table -->
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Layering Ducks</div>
      <div class="card-body">
        <p>These ducks are actively laying eggs and part of the production batch.</p>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Batch Name</th>
                <th>Type</th>
                <th>Count</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($layering_ducks as $duck): ?>
              <tr>
                <td><?php echo htmlspecialchars($duck['batch_name']); ?></td>
                <td>Layering</td>
                <td><?php echo $duck['count']; ?></td>
                <td>
                  <span class="badge bg-<?php echo $duck['status'] === 'active' ? 'success' : 'secondary'; ?>">
                    <?php echo ucfirst($duck['status']); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($duck['created_at'])); ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLayeringModal<?php echo $duck['id']; ?>">Edit</button>
                  <button class="btn btn-sm btn-outline-danger" onclick="deleteBatch(<?php echo $duck['id']; ?>)">Delete</button>
                </td>
              </tr>
              
              <!-- Edit Modal -->
              <div class="modal fade" id="editLayeringModal<?php echo $duck['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Layering Batch</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                      <div class="modal-body">
                        <input type="hidden" name="action" value="update_layering">
                        <input type="hidden" name="id" value="<?php echo $duck['id']; ?>">
                        <div class="mb-3">
                          <label class="form-label">Batch Name</label>
                          <input type="text" class="form-control" name="batch_name" value="<?php echo htmlspecialchars($duck['batch_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Count</label>
                          <input type="number" class="form-control" name="count" value="<?php echo $duck['count']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="status">
                            <option value="active" <?php echo $duck['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $duck['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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
      </div>
    </div>

    <!-- Add New Future Batch -->
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Add New Future Batch</div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <input type="hidden" name="action" value="add_future">
          <div class="col-md-4">
            <label for="batch_name" class="form-label">Batch Name</label>
            <input type="text" class="form-control" name="batch_name" required>
          </div>
          <div class="col-md-3">
            <label for="male_count" class="form-label">Male Count</label>
            <input type="number" class="form-control" name="male_count" required>
          </div>
          <div class="col-md-3">
            <label for="female_count" class="form-label">Female Count</label>
            <input type="number" class="form-control" name="female_count" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Add Batch</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Future Ducks Table -->
    <div class="card">
      <div class="card-header bg-success text-white">Future Ducks (Male & Female)</div>
      <div class="card-body">
        <p>These ducks are not yet laying. Used for expansion planning and sex-based tracking.</p>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Batch Name</th>
                <th>Male Count</th>
                <th>Female Count</th>
                <th>Total</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($future_ducks as $duck): ?>
              <tr>
                <td><?php echo htmlspecialchars($duck['batch_name']); ?></td>
                <td><?php echo $duck['male_count']; ?></td>
                <td><?php echo $duck['female_count']; ?></td>
                <td><?php echo $duck['male_count'] + $duck['female_count']; ?></td>
                <td>
                  <span class="badge bg-<?php echo $duck['status'] === 'future' ? 'info' : 'secondary'; ?>">
                    <?php echo ucfirst($duck['status']); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($duck['created_at'])); ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFutureModal<?php echo $duck['id']; ?>">Edit</button>
                  <button class="btn btn-sm btn-outline-danger" onclick="deleteBatch(<?php echo $duck['id']; ?>)">Delete</button>
                </td>
              </tr>
              
              <!-- Edit Modal -->
              <div class="modal fade" id="editFutureModal<?php echo $duck['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Future Batch</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                      <div class="modal-body">
                        <input type="hidden" name="action" value="update_future">
                        <input type="hidden" name="id" value="<?php echo $duck['id']; ?>">
                        <div class="mb-3">
                          <label class="form-label">Batch Name</label>
                          <input type="text" class="form-control" name="batch_name" value="<?php echo htmlspecialchars($duck['batch_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Male Count</label>
                          <input type="number" class="form-control" name="male_count" value="<?php echo $duck['male_count']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Female Count</label>
                          <input type="number" class="form-control" name="female_count" value="<?php echo $duck['female_count']; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="status">
                            <option value="future" <?php echo $duck['status'] === 'future' ? 'selected' : ''; ?>>Future</option>
                            <option value="active" <?php echo $duck['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $duck['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function deleteBatch(id) {
      if (confirm('Are you sure you want to delete this batch?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
</body>
</html>
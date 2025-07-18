<!-- frontend/navbar.php -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/frontend/dashboard.php">Task Manager</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="/frontend/tasks/create-task.php">Add Task</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/frontend/dashboard.php">Dashboard</a>
        </li>

        <!-- Show these links only if user is admin -->
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true): ?>
          <li class="nav-item">
            <a class="nav-link" href="/frontend/admin/admin-dashboard.php">Admin Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/frontend/admin/admin-users.php">User Management</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/frontend/admin/admin-tasks.php">Task Moderation</a>
          </li>
        <?php endif; ?>

        <li class="nav-item">
          <a class="nav-link text-danger" href="/backend/logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

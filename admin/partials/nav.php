<nav>
  <span class="brand">Swypply</span>
  <a href="/admin/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
  <a href="/admin/users.php"     class="<?= basename($_SERVER['PHP_SELF']) === 'users.php'     ? 'active' : '' ?>">Users</a>
  <a href="/admin/logout.php" class="logout">Sign out</a>
</nav>

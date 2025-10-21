<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pharmacist Management - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/admin/admin-style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo">
    <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo">
    <span class="logo-text">Medora Admin</span>
  </div>

  <ul class="nav-links">
    <li>
      <a href="${pageContext.request.contextPath}/admin/dashboard">
        <i data-lucide="layout-dashboard"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <li class="active">
      <a href="${pageContext.request.contextPath}/admin/pharmacists">
        <i data-lucide="user-check"></i>
        <span>Pharmacists</span>
      </a>
    </li>
    <li>
      <a href="${pageContext.request.contextPath}/admin/settings">
        <i data-lucide="settings"></i>
        <span>Settings</span>
      </a>
    </li>
  </ul>

  <div class="admin-profile">
    <div class="profile-icon">AD</div>
    <div class="profile-info">
      <p class="name">Admin User</p>
      <p class="email">admin@medora.com</p>
    </div>
  </div>
</aside>

<!-- Main Content -->
<main class="main-content">
  <header class="topbar">
    <div class="search-bar">
      <i data-lucide="search"></i>
      <input type="text" placeholder="Search users, pharmacists..." />
    </div>
    <div class="top-icons">
      <i data-lucide="bell"></i>
    </div>
  </header>

  <section class="pharmacist-section">
    <div class="section-header">
      <div>
        <h1>Pharmacist Management</h1>
        <p>Manage pharmacist accounts and permissions</p>
      </div>
      <button class="add-btn" onclick="window.location.href='${pageContext.request.contextPath}/admin/pharmacists/add'">
        <i data-lucide='plus'></i> Add Pharmacist
      </button>
    </div>

    <div class="stats-row">
      <div class="stat-card"><h3>Total Pharmacists</h3><h2>127</h2></div>
      <div class="stat-card"><h3>Active</h3><h2>98</h2></div>
      <div class="stat-card"><h3>Deleted</h3><h2>5</h2></div>
    </div>

    <div class="search-filter">
      <div class="search-box">
        <i data-lucide="search"></i>
        <input type="text" placeholder="Search by name, email, or license..." />
      </div>
    </div>

    <table class="pharmacist-table">
      <thead>
      <tr>
        <th>Name</th>
        <th>Contact</th>
        <th>License</th>
        <th>Joined Date</th>
        <th>Last Active</th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <!-- Sample Static Rows -->
      <tr>
        <td>Dr. Sarah Johnson</td>
        <td>sarah.johnson@medora.com<br /><span class="phone">+1 234-567-8901</span></td>
        <td>PHR-2024-001</td>
        <td>2024-01-15</td>
        <td>2 hours ago</td>
        <td>
          <button class="action-btn" onclick="openActionsMenu(this)">
            <i data-lucide="more-vertical"></i>
          </button>
          <div class="action-menu hidden">
            <ul>
              <li class="edit" onclick="window.location.href='${pageContext.request.contextPath}/admin/pharmacists/edit?id=1'">
                <i data-lucide='edit-3'></i>
                Edit Details
              </li>
              <li class="delete">
                <i data-lucide="trash-2"></i>
                Delete
              </li>
            </ul>
          </div>
        </td>
      </tr>
      <!-- Repeat for other entries -->
      </tbody>
    </table>
  </section>
</main>

<script>
  lucide.createIcons();

  function openActionsMenu(button) {
    const menu = button.nextElementSibling;
    document.querySelectorAll('.action-menu').forEach(m => {
      if (m !== menu) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
  }

  document.addEventListener('click', e => {
    if (!e.target.closest('.action-btn') && !e.target.closest('.action-menu')) {
      document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));
    }
  });
</script>

</body>
</html>

<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Pharmacist - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/admin/admin-style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

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
        <i data-lucide="user"></i>
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
      <div class="name">Admin User</div>
      <div class="email">admin@medora.com</div>
    </div>
  </div>
</aside>

<main class="main-content">
  <div class="topbar">
    <div class="search-bar">
      <i data-lucide="search"></i>
      <input type="text" placeholder="Search users, pharmacists..." />
    </div>
    <div class="top-icons">
      <i data-lucide="bell"></i>
    </div>
  </div>

  <section class="settings-section" style="margin-top: 35px;">
    <div class="settings-card wide">
      <div class="form-header">
        <i data-lucide="user-cog" class="form-icon"></i>
        <div>
          <h2>Edit Profile</h2>
          <p>Edit the profile of pharmacist</p>
        </div>
      </div>

      <form class="admin-form" action="${pageContext.request.contextPath}/admin/edit-pharmacist" method="post">
        <input type="hidden" name="id" value="${pharmacist.id}" />

        <div class="form-group">
          <label for="licenseNumber">License Number</label>
          <input type="text" id="licenseNumber" name="licenseNumber" value="${pharmacist.licenseNumber}" required />
        </div>

        <div class="form-group">
          <label for="fullName">Full Name</label>
          <input type="text" id="fullName" name="fullName" value="${pharmacist.fullName}" required />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter new password" />
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" />
        </div>

        <div class="form-group">
          <label for="contactNumber">Contact Number</label>
          <input type="text" id="contactNumber" name="contactNumber" value="${pharmacist.contactNumber}" required />
        </div>

        <div class="button-row" style="display:flex; gap:12px; margin-top:10px;">
          <button type="button" class="cancel-btn" onclick="window.location.href='${pageContext.request.contextPath}/admin/pharmacists'">Cancel</button>
          <button type="submit" class="save-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </section>
</main>

<script>
  lucide.createIcons();
</script>
</body>
</html>

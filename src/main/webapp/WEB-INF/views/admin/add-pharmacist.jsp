<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pharmacist - Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/admin/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <!-- Topbar -->
    <header class="topbar">
        <div class="search-bar">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Search users, pharmacists...">
        </div>
        <div class="top-icons">
            <i data-lucide="bell"></i>
        </div>
    </header>

    <!-- Add Pharmacist Form Section -->
    <section class="settings-section">
        <div class="settings-card wide">
            <div class="form-header">
                <i data-lucide="user-plus" class="form-icon"></i>
                <div>
                    <h2>Add Pharmacist</h2>
                    <p>Register a new pharmacist in the system</p>
                </div>
            </div>

            <form class="admin-form" action="${pageContext.request.contextPath}/admin/add-pharmacist" method="post">
                <div class="form-group">
                    <label for="license">License Number</label>
                    <input type="text" id="license" name="license" placeholder="Enter license number" required>
                </div>

                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter contact number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirmPassword" placeholder="Re-enter password" required>
                </div>

                <button type="submit" class="save-btn">Add Pharmacist</button>
            </form>
        </div>
    </section>
</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>

<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Medora</title>
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
        <li class="active">
            <a href="${pageContext.request.contextPath}/admin/dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
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

    <section class="dashboard">
        <h1>Dashboard</h1>
        <p class="subtitle">Welcome back! Here's what's happening today.</p>

        <div class="stats-grid">
            <div class="card">
                <div class="card-icon blue"><i data-lucide="users"></i></div>
                <div>
                    <h2>2,847</h2>
                    <p>Total Active Users</p>
                    <span class="trend up">+12.5% from last month</span>
                </div>
            </div>

            <div class="card">
                <div class="card-icon green"><i data-lucide="user-check"></i></div>
                <div>
                    <h2>127</h2>
                    <p>Active Pharmacists</p>
                    <span class="trend up">+4.2% from last month</span>
                </div>
            </div>

            <div class="card">
                <div class="card-icon purple"><i data-lucide="activity"></i></div>
                <div>
                    <h2>1,543</h2>
                    <p>Patients Today</p>
                    <span class="trend up">+8.1% from last month</span>
                </div>
            </div>

            <div class="card">
                <div class="card-icon pink"><i data-lucide="shield"></i></div>
                <div>
                    <h2>1,177</h2>
                    <p>Active Guardians</p>
                    <span class="trend up">+6.3% from last month</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Activity Section -->
    <section class="recent-activity">
        <div class="activity-card">
            <div class="activity-header">
                <i data-lucide="activity" class="activity-icon"></i>
                <div>
                    <h2>Recent Activity</h2>
                    <p>Latest system actions and events</p>
                </div>
            </div>

            <ul class="activity-list">
                <li>
                    <div class="activity-left">
                        <div class="activity-badge green"><i data-lucide="activity"></i></div>
                        <div>
                            <strong>Dr. Sarah Johnson</strong>
                            <p>Created new pharmacist account</p>
                        </div>
                    </div>
                    <span class="time">2 minutes ago</span>
                </li>
                <li>
                    <div class="activity-left">
                        <div class="activity-badge blue"><i data-lucide="activity"></i></div>
                        <div>
                            <strong>John Doe</strong>
                            <p>Updated patient profile</p>
                        </div>
                    </div>
                    <span class="time">15 minutes ago</span>
                </li>
                <li>
                    <div class="activity-left">
                        <div class="activity-badge red"><i data-lucide="activity"></i></div>
                        <div>
                            <strong>Admin User</strong>
                            <p>Deleted inactive pharmacist</p>
                        </div>
                    </div>
                    <span class="time">1 hour ago</span>
                </li>
                <li>
                    <div class="activity-left">
                        <div class="activity-badge green"><i data-lucide="activity"></i></div>
                        <div>
                            <strong>Jane Smith</strong>
                            <p>Added new guardian link</p>
                        </div>
                    </div>
                    <span class="time">2 hours ago</span>
                </li>
                <li>
                    <div class="activity-left">
                        <div class="activity-badge purple"><i data-lucide="activity"></i></div>
                        <div>
                            <strong>Dr. Mike Wilson</strong>
                            <p>Restored soft-deleted account</p>
                        </div>
                    </div>
                    <span class="time">3 hours ago</span>
                </li>
            </ul>
        </div>
    </section>

    <!-- System Health Section -->
    <section class="system-health">
        <div class="health-card">
            <div class="health-header">
                <i data-lucide="cpu" class="health-icon"></i>
                <div>
                    <h2>System Health</h2>
                    <p>Real-time system metrics</p>
                </div>
            </div>

            <div class="health-metric">
                <div class="metric-label">
                    <span>Active Sessions</span>
                    <span class="metric-value">847</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill blue" style="width: 70%;"></div>
                </div>
            </div>

            <div class="health-metric">
                <div class="metric-label">
                    <span>API Response Time</span>
                    <span class="metric-value green">142ms</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill green" style="width: 95%;"></div>
                </div>
            </div>

            <div class="health-metric">
                <div class="metric-label">
                    <span>Error Rate</span>
                    <span class="metric-value red">0.2%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill red" style="width: 5%;"></div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>

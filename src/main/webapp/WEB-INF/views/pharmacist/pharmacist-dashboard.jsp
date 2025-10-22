<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medora - Dashboard</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
</head>
<body>
<div class="container">
    <!-- Include Sidebar Component -->
    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="user-info">
                <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar" class="avatar">
                <span class="user-role">Super Pharmacist</span>
            </div>
            <div class="greeting">
                <span class="greeting-icon">☀️</span>
                <div>
                    <span class="greeting-text">Good Morning</span>
                    <span class="date-time">14 January 2022 • 22:45:04</span>
                </div>
            </div>
        </header>

        <!-- Page Title -->
        <h2 class="page-title">Dashboard</h2>

        <!-- Metric Cards -->
        <div class="metric-cards">
            <div class="metric-card">
                <div class="metric-value">21</div>
                <div class="metric-label">Pending Prescriptions</div>
                <a href="#" class="see-all">See all »</a>
            </div>
            <div class="metric-card">
                <div class="metric-value">19</div>
                <div class="metric-label">Pending Schedules</div>
                <a href="#" class="see-all">See All »</a>
            </div>
            <div class="metric-card">
                <div class="metric-value">10</div>
                <div class="metric-label">New patients (last 24 hrs)</div>
                <a href="#" class="see-all">See All »</a>
            </div>
        </div>

        <!-- Patient Table 1: Check Actions -->
        <div class="table-container">
            <div class="table-header">
                <h3>Patient List — Check Required</h3>
                <a href="#" class="see-all-link">See All »</a>
            </div>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Chronic Condition</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Ethan Mitchell</td>
                    <td>Diabetes</td>
                    <td><a href="#" class="action-link">Check</a></td>
                </tr>
                <tr>
                    <td>Ava Reynolds</td>
                    <td>Diabetes</td>
                    <td><a href="#" class="action-link">Check</a></td>
                </tr>
                <tr>
                    <td>Owen Foster</td>
                    <td>Diabetes</td>
                    <td><a href="#" class="action-link">Check</a></td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Patient Table 2: Schedule Actions -->
        <div class="table-container">
            <div class="table-header">
                <h3>Patient List — Schedule Required</h3>
                <a href="#" class="see-all-link">See All »</a>
            </div>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Chronic Condition</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Ethan Mitchell</td>
                    <td>Diabetes</td>
                    <td><a href="#" class="action-link">Schedule</a></td>
                </tr>
                <tr>
                    <td>Ava Reynolds</td>
                    <td>Diabetes</td>
                    <td><a href="#" class="action-link">Schedule</a></td>
                </tr>
                <tr>
                    <td>Owen Foster</td>
                    <td>Diabetes</td>
                    <td><a href="#" class="action-link">Schedule</a></td>
                </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
<%@ page contentType="text/html;charset=UTF-8" %>
<!-- Sidebar for pharmacist dashboard -->
<link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
<aside class="sidebar">
    <div class="logo-section">
        <div class="logo-icon">✚</div>
        <h1 class="logo-text">Medora</h1>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="<%= request.getContextPath() %>/pharmacist/dashboard" class="nav-item <%= request.getRequestURI().endsWith("dashboard") ? "active" : "" %>"><span class="icon">📋</span> Dashboard</a></li>
            <li><a href="<%= request.getContextPath() %>/pharmacist/validate" class="nav-item"><span class="icon">💊</span> Prescription Review</a></li>
            <li><a href="${pageContext.request.contextPath}/pharmacist/approved-prescriptions" class="nav-item"><i class="icon">✔️</i><span>Approved Prescriptions</span></a></li>
            <li><a href="#" class="nav-item"><span class="icon">👤</span> Patients</a></li>
            <li><a href="#" class="nav-item"><span class="icon">📈</span> Reports</a></li>
            <li><a href="#" class="nav-item"><span class="icon">🔔</span> Notifications</a></li>
            <li><a href="#" class="nav-item"><span class="icon">⚙️</span> Application Settings</a></li>
        </ul>
    </nav>

    <div class="footer-section">
        <a href="${pageContext.request.contextPath}/logout?next=/pharmacist/login" class="nav-item logout-link" style="display:block; margin-top:10px;">Logout</a>
        <div class="copyright">Medora © 2022</div>
        <div class="version">v 1.1.2</div>
    </div>
</aside>
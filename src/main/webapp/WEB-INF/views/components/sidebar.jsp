<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%
    // Optional: You can pass user role or name from servlet if needed
    String userRole = (String) request.getAttribute("userRole");
    if (userRole == null) userRole = "Super Pharmacist";
%>

<aside class="sidebar">
    <div class="logo-section">
        <div class="logo-icon">✚</div>
        <h1 class="logo-text">Medora</h1>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="<%= request.getContextPath() %>/pharmacist/dashboard" class="nav-item <%= request.getRequestURI().endsWith("dashboard") ? "active" : "" %>"><span class="icon">📋</span> Dashboard</a></li>
            <li><a href="<%= request.getContextPath() %>/pharmacist/prescription-review" class="nav-item <%= request.getRequestURI().endsWith("prescription-review") ? "active" : "" %>"><span class="icon">💊</span> Prescription Review</a></li>
            <li><a href="#" class="nav-item"><span class="icon">📅</span> Scheduling</a></li>
            <li><a href="#" class="nav-item"><span class="icon">👤</span> Patients</a></li>
            <li><a href="#" class="nav-item"><span class="icon">📈</span> Reports</a></li>
            <li><a href="#" class="nav-item"><span class="icon">🔔</span> Notifications</a></li>
            <li><a href="#" class="nav-item"><span class="icon">⚙️</span> Application Settings</a></li>
        </ul>
    </nav>

    <div class="footer-section">
        <a href="#" class="help-link"><span class="icon">❓</span> Get Technical Help</a>
        <div class="copyright">Medora © 2022</div>
        <div class="version">v 1.1.2</div>
    </div>
</aside>
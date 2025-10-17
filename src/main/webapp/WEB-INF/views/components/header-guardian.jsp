<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">

<header class="header">
    <div class="logo">
        <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo">
        <span>Medora</span>
    </div>

    <nav class="nav-links">
        <a href="${pageContext.request.contextPath}/guardian/dashboard">
            Dashboard
        </a>
        <a href="${pageContext.request.contextPath}/guardian/patients">
            Patients
        </a>
        <a href="${pageContext.request.contextPath}/guardian/reports">
            Reports
        </a>
        <a href="${pageContext.request.contextPath}/guardian/alerts">
            Alerts
        </a>
        <a href="${pageContext.request.contextPath}/guardian/profile">
            Profile
        </a>
        <a href="${pageContext.request.contextPath}/logout">
            Logout
        </a>
    </nav>
</header>

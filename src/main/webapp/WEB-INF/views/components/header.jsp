<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">

<header class="header">
    <div class="logo">
        <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo">
        <span>Medora</span>
    </div>

    <nav class="nav-links">
        <a href="${pageContext.request.contextPath}/patient/dashboard">Dashboard</a>
        <a href="${pageContext.request.contextPath}/patient/upload-prescription">Prescriptions</a>
        <a href="${pageContext.request.contextPath}/patient/adherence-history">History</a>
        <a href="${pageContext.request.contextPath}/patient/notifications">Notifications</a>
        <a href="${pageContext.request.contextPath}/patient/profile">Profile</a>
        <a href="${pageContext.request.contextPath}/logout?next=/login">Logout</a>
    </nav>
</header>

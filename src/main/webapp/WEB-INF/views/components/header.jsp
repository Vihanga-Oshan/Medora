<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">

<header class="header">
  <div class="logo">
    <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo">
    <span>Smart Prescription Tracker</span>
  </div>

  <nav class="nav-links">
    <a href="${pageContext.request.contextPath}/patient/dashboard">
<%--      <img src="${pageContext.request.contextPath}/assets/icons/dashboard.svg" width="18" alt="Dashboard Icon">--%>
      Dashboard
    </a>
    <a href="${pageContext.request.contextPath}/patient/upload-prescription">
<%--      <img src="${pageContext.request.contextPath}/assets/icons/prescription.svg" width="18" alt="Prescription Icon">--%>
      Prescriptions
    </a>
    <a href="${pageContext.request.contextPath}/patient/adherence-history">
<%--      <img src="${pageContext.request.contextPath}/assets/icons/history.svg" width="18" alt="History Icon">--%>
      History
    </a>
    <a href="${pageContext.request.contextPath}/patient/notifications">
<%--      <img src="${pageContext.request.contextPath}/assets/icons/notification.svg" width="18" alt="Notification Icon">--%>
      Notifications
    </a>
    <a href="${pageContext.request.contextPath}/patient/profile">
<%--      <img src="${pageContext.request.contextPath}/assets/icons/user.svg" width="18" alt="User Icon">--%>
      Profile
    </a>
    <a href="${pageContext.request.contextPath}/logout">
<%--      <img src="${pageContext.request.contextPath}/assets/icons/logout.svg" width="18" alt="Logout Icon">--%>
      Logout
    </a>
  </nav>
</header>

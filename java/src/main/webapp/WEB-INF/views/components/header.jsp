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
            <a href="${pageContext.request.contextPath}/patient/messages">Messages</a>

            <a href="${pageContext.request.contextPath}/patient/profile">Profile</a>
            <a href="${pageContext.request.contextPath}/logout?next=/login">Logout</a>
        </nav>
    </header>

    <!-- Active Link Highlighting -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-links a');
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && !href.includes('logout') && currentPath.includes(href)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    <div class="decorative-blob blob-1"></div>
    <div class="decorative-blob blob-2"></div>
    <div class="decorative-blob blob-3"></div>
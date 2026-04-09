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
                <li><a href="${pageContext.request.contextPath}/pharmacist/dashboard"
                        class="nav-item ${pageContext.request.requestURI.endsWith('dashboard.jsp') or pageContext.request.requestURI.contains('/dashboard') ? 'active' : ''}">Dashboard</a>
                </li>
                <li><a href="${pageContext.request.contextPath}/pharmacist/validate"
                        class="nav-item ${pageContext.request.requestURI.contains('/validate') ? 'active' : ''}">Prescription
                        Review</a></li>
                <li><a href="${pageContext.request.contextPath}/pharmacist/approved-prescriptions"
                        class="nav-item ${pageContext.request.requestURI.contains('/approved-prescriptions') ? 'active' : ''}"><span>Approved
                            Prescriptions</span></a></li>
                <li><a href="${pageContext.request.contextPath}/pharmacist/patients"
                        class="nav-item ${pageContext.request.requestURI.contains('/patients') ? 'active' : ''}">Patients</a>
                </li>
                <li><a href="${pageContext.request.contextPath}/pharmacist/messages"
                        class="nav-item ${pageContext.request.requestURI.contains('/messages') ? 'active' : ''}">
                        Messages
                        <c:if test="${not empty unreadMessagesCount and unreadMessagesCount > 0}">
                            <span class="nav-badge">${unreadMessagesCount}</span>
                        </c:if>
                    </a>
                </li>
                <li><a href="${pageContext.request.contextPath}/pharmacist/medicine-inventory"
                        class="nav-item ${pageContext.request.requestURI.contains('/medicine-inventory') ? 'active' : ''}">Medicine</a>
                </li>
                <li><a href="#" class="nav-item">Settings</a></li>
            </ul>
        </nav>

        <div class="footer-section">
            <a href="${pageContext.request.contextPath}/logout?next=/pharmacist/login" class="nav-item logout-link"
                style="display:block; margin-top:10px;">Logout</a>
            <div class="copyright">Medora © 2022</div>
            <div class="version">v 1.1.2</div>
        </div>
    </aside>
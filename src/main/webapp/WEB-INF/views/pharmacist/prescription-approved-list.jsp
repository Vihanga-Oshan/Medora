<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
    <%@ page contentType="text/html;charset=UTF-8" language="java" %>
        <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
            <!DOCTYPE html>
            <html>

            <head>
                <title>Approved Prescriptions - Medora</title>
                <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
                <link rel="stylesheet"
                    href="${pageContext.request.contextPath}/css/pharmacist/prescription-validation.css">
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap"
                    rel="stylesheet">
                <style>
                    /* Specificity override for the main layout container */
                    body div.container {
                        display: flex !important;
                        width: 100% !important;
                        max-width: none !important;
                        height: 100vh !important;
                        margin: 0 !important;
                    }
                </style>
            </head>

            <body>
                <div class="container">
                    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

                        <div class="main-content">
                            <header class="header">
                                <div class="user-info">
                                    <img src="${pageContext.request.contextPath}/assets/register-patient1.png"
                                        alt="User Avatar" class="avatar">
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

                            <div class="validation-page-body">
                                <div class="pv-header">
                                    <h2 class="page-title">Approved Prescriptions</h2>
                                    <p class="subtitle">Prepare schedules for validated prescriptions</p>
                                </div>

                                <c:if test="${empty approvedPrescriptions}">
                                    <div class="no-data-card">
                                        <span class="no-data-icon">✅</span>
                                        <p>No approved prescriptions pending scheduling.</p>
                                        <a href="${pageContext.request.contextPath}/pharmacist/dashboard"
                                            class="btn primary">Back
                                            to Dashboard</a>
                                    </div>
                                </c:if>

                                <div class="prescription-grid">
                                    <c:forEach var="p" items="${approvedPrescriptions}">
                                        <div class="prescription-card">
                                            <div class="preview-container">
                                                <c:choose>
                                                    <c:when test="${fn:endsWith(p.fileName, '.pdf')}">
                                                        <div class="pdf-thumb">
                                                            <span class="pdf-icon">📄</span>
                                                            <span class="pdf-text">PDF</span>
                                                        </div>
                                                    </c:when>
                                                    <c:otherwise>
                                                        <c:set var="cleanFilePath"
                                                            value="${fn:replace(p.filePath, '\"', '')}" />
                                    <img src="${pageContext.request.contextPath}/prescriptionFile/${cleanFilePath}" alt="Prescription" class="preview-image" />
                                </c:otherwise>
                            </c:choose>
                        </div>

                        <div class="card-info">
                            <div class="info-group">
                                <span class="info-label">Patient NIC</span>
                                <span class="info-value">${p.patientNic}</span>
                            </div>
                            <div class="info-group">
                                <span class="info-label">Approved On</span>
                                <span class="info-value">${p.getFormattedUploadDate()}</span>
                            </div>
                            <div class="info-group">
                                <span class="file-name" title="${p.fileName}">${p.fileName}</span>
                            </div>
                        </div>

                        <div class="card-footer">
                            <a href="${pageContext.request.contextPath}/pharmacist/schedule?id=${p.id}&nic=${p.patientNic}" class="view-details-btn">
                                <span>Schedule Medicine</span>
                                <span class="arrow">→</span>
                            </a>
                        </div>
                    </div>
                </c:forEach>
            </div>
        </div>
    </div>
</div>
</body>
</html>
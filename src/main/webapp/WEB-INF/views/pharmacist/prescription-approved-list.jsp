<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html>
<head>
    <title>Validate Prescriptions - Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/prescription-validation.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">
    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

    <div class="main-content prescription-validation-page">
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
        <div class="validation-container">
            <div class="pv-header">
                <h2>Prescription Validation</h2>

            </div>

            <c:if test="${empty approvedPrescriptions}">
                <p class="no-data-msg">No pending prescriptions.</p>
                <a href="${pageContext.request.contextPath}/pharmacist/dashboard" class="back-link">← Back to Dashboard</a>
            </c:if>

            <div class="prescription-grid">
                <c:forEach var="p" items="${approvedPrescriptions}">
                    <div class="prescription-card">
                        <div class="preview-container">
                            <c:choose>
                                <c:when test="${fn:endsWith(p.fileName, '.pdf')}">
                                    <span class="pdf-icon">📄</span>
                                </c:when>
                                <c:otherwise>
                                    <img src="${pageContext.request.contextPath}/prescriptionFile/${p.filePath}"
                                    alt="Prescription" class="preview-image" />
                                </c:otherwise>
                            </c:choose>
                        </div>

                        <div class="info">
                            <p><strong>Patient:</strong> ${p.patientNic}</p>
                            <p><strong>Uploaded:</strong> ${p.uploadDate}</p>
                            <p class="file-type">${p.fileName}</p>
                        </div>

                        <a href="${pageContext.request.contextPath}/pharmacist/schedule?id=${p.id}&nic=${p.patientNic}">
                            <button type="submit" class="btn btn-approve">Schedule</button>
                        </a>


                    </div>
                </c:forEach>


            </div>

        </div>
    </div>
</div>
</body>
</html>
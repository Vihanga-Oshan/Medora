<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html>
<head>
    <title>Validate Prescriptions - Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/prescription-validation.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">
    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

    <div class="main-content prescription-validation-page">
        <div class="validation-container">
            <div class="pv-header">
                <h2>Prescription Validation</h2>
                <a href="${pageContext.request.contextPath}/pharmacist/dashboard" class="back-link">← Back to Dashboard</a>
            </div>

            <c:if test="${empty prescriptions}">
                <p class="no-data-msg">No pending prescriptions.</p>
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
                                    <img src="${pageContext.request.contextPath}/view-prescription?filePath=${p.filePath}"
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
                            <button>Schedule</button>
                        </a>


                    </div>
                </c:forEach>


            </div>

        </div>
    </div>
</div>
</body>
</html>
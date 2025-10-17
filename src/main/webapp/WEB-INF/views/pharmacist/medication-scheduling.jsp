<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>

<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html>
<head>
    <title>Medication Scheduling</title>

    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/medication-scheduling.css">
</head>
<body>
<div class="container">
    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

    <div class="main-content">
        <h1>Medication Scheduling</h1>

        <div class="scheduling-grid">
            <form action="${pageContext.request.contextPath}/submit-schedule" method="post" class="schedule-form">
                <input type="hidden" name="prescriptionId" value="${prescription.id}">

                <label>Medicine Name</label>
                <input type="text" name="medicine_name" required>

                <label>Dosage</label>
                <input type="text" name="dosage" required>

                <label>Days</label>
                <input type="text" name="days" required>

                <label>Administration times</label>
                <input type="text" name="frequency_cron" required>

                <label>Meal timing</label>
                <input type="text" name="meal_timing">

                <label>Special Instructions</label>
                <textarea name="instructions" rows="4"></textarea>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">Submit Schedule</button>
                    <a href="${pageContext.request.contextPath}/pharmacist/dashboard" class="btn-reject">Reject</a>
                </div>
            </form>

            <div class="patient-details-box">
                <div class="preview-placeholder">
                    <c:choose>
                        <c:when test="${fn:endsWith(prescription.fileName, '.pdf')}">
                            <span class="pdf-icon">📄 PDF</span>
                        </c:when>
                        <c:otherwise>
                            <img src="${pageContext.request.contextPath}/view-prescription?filePath=${prescription.filePath}"
                                 alt="Prescription" style="max-height: 250px;" />
                        </c:otherwise>
                    </c:choose>
                </div>

                <h3>Patient NIC: ${prescription.patientNic}</h3>
                <p><strong>Uploaded:</strong> ${prescription.uploadDate}</p>
                <p><strong>File:</strong> ${prescription.fileName}</p>


                <h3>Patient Details</h3>
                <div class="info-group">
                    <div class="info-card">
                        <p class="label">Patient Name</p>
                        <p>${patient.name}</p>
                    </div>
                    <div class="info-card">
                        <p class="label">Contact</p>
                        <p>${patient.contact}</p>
                    </div>
                    <div class="info-card">
                        <p class="label">Age</p>
                        <p>${patient.age}</p>
                    </div>
                </div>

<%--                <div class="current-meds">--%>
<%--                    <p class="label">Current Medications</p>--%>
<%--                    <p>${prescription.medicineSummary}</p>--%>
<%--                </div>--%>
            </div>
        </div>
    </div>
</div>
</body>
</html>


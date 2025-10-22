<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Medication Schedule</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/medication-scheduling.css">
</head>
<body>
<div class="container">
    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

    <div class="main-content">

        <h1>Edit Medication Schedule</h1>

        <div class="scheduling-grid">

            <!-- ======= Edit Form Section ======= -->
            <form action="${pageContext.request.contextPath}/pharmacist/edit-schedule"
                  method="post" class="schedule-form">

                <input type="hidden" name="id" value="${schedule.id}">
                <input type="hidden" name="nic" value="${param.nic}">

                <h3>Medicine Details</h3>

                <div class="med-row">
                    <label>Medicine</label>
                    <input type="text" name="medicineName" value="${schedule.medicineName}" readonly />

                    <label>Dosage</label>
                    <select name="dosage" required>
                        <option value="${schedule.dosage}" selected>${schedule.dosage}</option>
                        <c:forEach var="d" items="${dosages}">
                            <c:if test="${d[1] ne schedule.dosage}">
                                <option value="${d[1]}">${d[1]}</option>
                            </c:if>
                        </c:forEach>
                    </select>

                    <label>Frequency</label>
                    <select name="frequency" required>
                        <option value="${schedule.frequency}" selected>${schedule.frequency}</option>
                        <c:forEach var="f" items="${frequencies}">
                            <c:if test="${f[1] ne schedule.frequency}">
                                <option value="${f[1]}">${f[1]}</option>
                            </c:if>
                        </c:forEach>
                    </select>

                    <label>Meal Timing</label>
                    <select name="mealTiming">
                        <option value="${schedule.mealTiming}" selected>${schedule.mealTiming}</option>
                        <c:forEach var="mt" items="${mealTimings}">
                            <c:if test="${mt[1] ne schedule.mealTiming}">
                                <option value="${mt[1]}">${mt[1]}</option>
                            </c:if>
                        </c:forEach>
                    </select>

                    <label>Start Date</label>
                    <input type="date" name="startDate" value="${schedule.startDate}" required>

                    <label>Duration (Days)</label>
                    <input type="number" name="durationDays" value="${schedule.durationDays}" min="1" required>

                    <label>Instructions</label>
                    <textarea name="instructions" rows="2">${schedule.instructions}</textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-submit">💾 Save Changes</button>
                    <a href="${pageContext.request.contextPath}/pharmacist/view-schedule?nic=${param.nic}" class="btn-reject">Cancel</a>
                </div>
            </form>

            <!-- ======= Prescription Preview Section ======= -->
            <div class="patient-details-box">
                <h3>Linked Prescription</h3>
                <div class="preview-placeholder">
                    <c:choose>
                        <c:when test="${empty prescription}">
                            <p style="color:#999;">No prescription file found.</p>
                        </c:when>
                        <c:when test="${fn:endsWith(prescription.fileName, '.pdf')}">
                            <span class="pdf-icon">📄 PDF</span>
                            <p>${prescription.fileName}</p>
                        </c:when>
                        <c:otherwise>
                            <img src="${pageContext.request.contextPath}/prescriptionFile/${prescription.filePath}"
                                 alt="Prescription" class="preview-image" />
                        </c:otherwise>
                    </c:choose>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>

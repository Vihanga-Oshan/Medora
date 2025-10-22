<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html>
<head>
  <title>View Medication Schedule - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/view-schedule.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">
  <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

  <div class="main-content">
    <!-- ===== Header Section ===== -->
    <header class="header">
      <div class="user-info">
        <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar" class="avatar">
        <span class="user-role">Pharmacist</span>
      </div>
      <div class="greeting">
        <span class="greeting-icon">📅</span>
        <div>
          <span class="greeting-text">Schedule Viewer</span>
          <span class="date-time">${selectedDate}</span>
        </div>
      </div>
    </header>

    <!-- ===== Page Content ===== -->
    <div class="schedule-viewer">

      <!-- ===== Patient Info Card ===== -->
      <div class="patient-info-card">
        <h2>Patient Details</h2>
        <p><strong>Name:</strong> ${patient.name}</p>
        <p><strong>NIC:</strong> ${patient.nic}</p>
        <p><strong>Gender:</strong> ${patient.gender}</p>
        <p><strong>Email:</strong> ${patient.email}</p>
        <p><strong>Emergency Contact:</strong> ${patient.emergencyContact}</p>
        <p><strong>Chronic Issues:</strong> ${patient.chronicIssues}</p>
        <p><strong>Allergies:</strong> ${patient.allergies}</p>
      </div>

      <!-- ===== Date Filter ===== -->
      <div class="date-filter">
        <form method="get" action="${pageContext.request.contextPath}/pharmacist/view-schedule">
          <input type="hidden" name="nic" value="${patient.nic}">
          <label for="date">Select Date:</label>
          <input type="date" id="date" name="date" value="${selectedDate}">
          <button type="submit">View</button>
        </form>
      </div>

      <!-- ===== Medication Schedule Table ===== -->
      <div class="schedule-table">
        <h2>Medication Schedule for ${selectedDate}</h2>

        <c:if test="${empty scheduleList}">
          <p class="no-data-msg">No medications scheduled for this day.</p>
        </c:if>

        <c:if test="${not empty scheduleList}">
          <table>
            <thead>
            <tr>
              <th>Time Slot</th>
              <th>Medicine</th>
              <th>Dosage</th>
              <th>Meal Timing</th>
              <th>Instructions</th>
              <th>Status</th>
              <th>Action</th> <!-- ✅ new -->
            </tr>
            </thead>
            <tbody>
            <c:forEach var="m" items="${scheduleList}">
              <tr>
                <td>${m.frequency}</td>
                <td>${m.medicineName}</td>
                <td>${m.dosage}</td>
                <td>${m.mealTiming}</td>
                <td>${m.instructions}</td>
                <td>
                  <c:choose>
                    <c:when test="${m.status == 'TAKEN'}">✅ Taken</c:when>
                    <c:when test="${m.status == 'MISSED'}">❌ Missed</c:when>
                    <c:otherwise>⏳ Pending</c:otherwise>
                  </c:choose>
                </td>
                <td>
                  <a href="${pageContext.request.contextPath}/pharmacist/edit-schedule?id=${m.id}&nic=${patient.nic}"
                     class="btn-edit">Edit</a>
                </td>
              </tr>
            </c:forEach>
            </tbody>
          </table>
        </c:if>
      </div>

    </div>
  </div>
</div>
</body>
</html>

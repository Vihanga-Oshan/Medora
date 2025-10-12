<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medora - Prescription Review</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/style.css">
</head>
<body>
<div class="container">

  <!-- Include Sidebar Component -->
  <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Header -->
    <header class="header">
      <div class="user-info">
        <img src="https://via.placeholder.com/40" alt="User Avatar" class="avatar">
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

    <!-- Page Title -->
    <h2 class="page-title">Prescription Review</h2>


    <!-- Content Area -->
    <div class="content-wrapper">
      <div class="prescription-image">
        <div class="image-placeholder">
          <c:choose>
            <c:when test="${fn:endsWith(prescription.fileName, '.pdf')}">
              <svg width="100%" height="100%" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <rect x="10" y="10" width="180" height="180" rx="20" fill="#e6eef7" stroke="#000" stroke-width="2"/>
                <circle cx="60" cy="60" r="15" fill="none" stroke="#000" stroke-width="2"/>
                <line x1="30" y1="170" x2="170" y2="90" stroke="#000" stroke-width="2"/>
              </svg>
            </c:when>
            <c:otherwise>
              <img src="${pageContext.request.contextPath}/view-prescription?filePath=${prescription.filePath}"
                   alt="Prescription"
                   style="width: 100%; height: 100%; object-fit: contain; display: block;">
            </c:otherwise>
          </c:choose>
        </div>
      </div>

      <div class="patient-details">
        <h3>Patient Details</h3>
        <div class="details-box">
          <p><strong>Full Name:</strong> ${patientName}</p>
          <p><strong>DOB:</strong> ${patientDOB}</p>
          <p><strong>MRN:</strong> ${patientMRN}</p>
          <p><strong>Prescribed By:</strong> ${prescribedBy}</p>
          <p><strong>Medication:</strong> ${medication}</p>
          <p><strong>Quantity:</strong> ${quantity}</p>
        </div>

        <!-- ✅ Action Buttons MOVED HERE -->
        <div class="action-buttons" style="margin-top: 20px; text-align: center;">
          <form action="${pageContext.request.contextPath}/pharmacist/prescription-review" method="post" style="display: inline;">
            <input type="hidden" name="prescriptionId" value="${prescription.id}">
            <input type="hidden" name="action" value="REJECT">
            <button type="submit" class="btn btn-reject">Reject</button>
          </form>
          <form action="${pageContext.request.contextPath}/pharmacist/prescription-review" method="post" style="display: inline; margin-left: 10px;">
            <input type="hidden" name="prescriptionId" value="${prescription.id}">
            <input type="hidden" name="action" value="APPROVE">
            <button type="submit" class="btn btn-approve">Approve</button>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>
</body>
</html>
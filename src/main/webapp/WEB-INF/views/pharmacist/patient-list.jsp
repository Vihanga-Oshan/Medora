<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html>
<head>
  <title>All Patients - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/patient-list.css">
</head>
<body>
<div class="container">
  <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

  <div class="main-content">
    <header class="header">
      <div class="user-info">
        <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar" class="avatar">
        <span class="user-role">Pharmacist</span>
      </div>
      <div class="greeting">
        <span class="greeting-icon">🧑‍⚕️</span>
        <div>
          <span class="greeting-text">All Patients</span>
        </div>
      </div>
    </header>

    <div class="table-section">
      <div class="table-header">
        <h2>Registered Patients</h2>
      <div class="search-bar">
        <form method="get" action="${pageContext.request.contextPath}/pharmacist/view-schedule">
        <input type="text" name="nic" placeholder="Search by NIC..." />
          <button type="submit">Search</button>
        </form>
      </div>
      </div>
      <c:if test="${empty patientList}">
        <p class="no-data-msg">No patients found.</p>
      </c:if>

      <c:if test="${not empty patientList}">
        <table class="patient-table">
          <thead>
          <tr>
            <th>Name</th>
            <th>NIC</th>
            <th>Email</th>
            <th>Emergency Contact</th>
            <th>Action</th>
          </tr>
          </thead>
          <tbody>
          <c:forEach var="p" items="${patientList}">
            <tr>
              <td>${p.name}</td>
              <td>${p.nic}</td>
              <td>${p.email}</td>
              <td>${p.emergencyContact}</td>
              <td>
                <a href="${pageContext.request.contextPath}/pharmacist/view-schedule?nic=${p.nic}"
                   class="btn-view">View Schedule</a>
              </td>
            </tr>
          </c:forEach>
          </tbody>
        </table>
      </c:if>
    </div>
  </div>
</div>
</body>
</html>

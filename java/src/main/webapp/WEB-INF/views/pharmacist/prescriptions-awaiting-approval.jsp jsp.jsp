<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medora - Prescriptions Awaiting Approval</title>
  <link rel="stylesheet" href="<%= request.getContextPath() %>/css/pharmacist/prescriptionsawaitingapproval.css">
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

    <!-- Page Title + Search Bar -->
    <div class="page-header">
      <h2 class="page-title">Prescriptions Awaiting Approval</h2>
      <div class="search-bar">
        <input type="text" placeholder="Search in Prescription Requests" class="search-input">
        <button class="search-button">🔍</button>
      </div>
    </div>

    <!-- Patient Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
        <tr>
          <th>Patient Name</th>
          <th>Chronic Condition</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td>Ethan Mitchell</td>
          <td>Diabetes</td>
          <td><a href="#" class="action-link">Check</a></td>
        </tr>
        <tr>
          <td>Ava Reynolds</td>
          <td>Diabetes</td>
          <td><a href="#" class="action-link">Check</a></td>
        </tr>
        <tr>
          <td>Owen Foster</td>
          <td>Diabetes</td>
          <td><a href="#" class="action-link">Check</a></td>
        </tr>
        <tr>
          <td>Ethan Mitchell</td>
          <td>Diabetes</td>
          <td><a href="#" class="action-link">Check</a></td>
        </tr>
        <tr>
          <td>Ava Reynolds</td>
          <td>Diabetes</td>
          <td><a href="#" class="action-link">Check</a></td>
        </tr>
        <tr>
          <td>Owen Foster</td>
          <td>Diabetes</td>
          <td><a href="#" class="action-link">Check</a></td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- See All Link (bottom right) -->
    <div class="see-all-footer">
      <a href="#" class="see-all-link">See All »</a>
    </div>
  </main>
</div>
</body>
</html>
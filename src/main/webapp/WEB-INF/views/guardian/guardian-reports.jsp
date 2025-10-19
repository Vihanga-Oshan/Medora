<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adherence Reports | Medora</title>

  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css"/>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/reports.css"/>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/main.css" />
</head>
<body>

<jsp:include page="/WEB-INF/views/components/header-guardian.jsp"/>

<main class="container">
  <div class="flex justify-between align-center mb-2">
    <div>
      <h1 class="section-title">Adherence Reports</h1>
      <p class="section-subtitle">View and analyze medication adherence trends and statistics</p>
    </div>
    <button class="btn btn-primary">📥 Export Report</button>
  </div>

  <!-- Patient Selector -->
  <div class="card">
    <h3>Select Patient</h3>
    <div class="flex mt-2">
      <div class="patient-selector active">Eleanor Rodriguez<br/><span class="text-sm">92% adherence</span></div>
      <div class="patient-selector">Robert Chen<br/><span class="text-sm">78% adherence</span></div>
      <div class="patient-selector">Margaret Wilson<br/><span class="text-sm">95% adherence</span></div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="flex justify-between mb-2">
    <div class="card stat-card primary">
      <h3>Adherence Rate</h3>
      <div class="stat-value">92% <span class="trend-up">↑</span></div>
      <p class="text-sm">Last 30 Days</p>
    </div>
    <div class="card stat-card success">
      <h3>Doses Taken</h3>
      <div class="stat-value">110</div>
      <p class="text-sm">Successfully administered</p>
    </div>
    <div class="card stat-card danger">
      <h3>Doses Missed</h3>
      <div class="stat-value">10</div>
      <p class="text-sm">Requires attention</p>
    </div>
    <div class="card stat-card primary">
      <h3>Total Doses</h3>
      <div class="stat-value">120</div>
      <p class="text-sm">Scheduled doses</p>
    </div>
  </div>

  <!-- Adherence Trend -->
  <div class="card">
    <h2>Adherence Trend</h2>
    <p class="section-subtitle">Weekly adherence for Eleanor Rodriguez</p>
    <div class="chart-placeholder">[SIMULATED CHART]</div>
  </div>

  <!-- Report Summary -->
  <div class="card">
    <h2>Report Summary</h2>
    <div class="report-summary-item">
      <div class="flex justify-between"><strong>Patient Name</strong><span>Eleanor Rodriguez</span></div>
      <div class="flex justify-between"><strong>Report Period</strong><span>Last 30 Days</span></div>
    </div>
    <div class="report-summary-item">
      <div class="flex justify-between"><strong>Overall Adherence</strong><span class="highlight">92%</span></div>
    </div>
    <div class="report-summary-item">
      <div class="flex justify-between"><strong>Total Scheduled Doses</strong><span>120</span></div>
    </div>
    <div class="report-summary-item taken">
      <div class="flex justify-between"><strong>Doses Taken</strong><span>110</span></div>
    </div>
    <div class="report-summary-item missed">
      <div class="flex justify-between"><strong>Doses Missed</strong><span>10</span></div>
    </div>

    <button class="btn btn-primary full-width mt-2">📥 Download Full Report (PDF)</button>
  </div>
</main>

<footer class="footer">
  <p>&copy; 2025 Medora. All rights reserved.</p>
</footer>

</body>
</html>

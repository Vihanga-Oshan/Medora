<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Guardian Dashboard | Medora</title>

  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/dashboard.css"/>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css"/>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/main.css"/>
</head>
<body>

<jsp:include page="/WEB-INF/views/components/header-guardian.jsp" />

<main class="container">
  <h1 class="section-title">Guardian Dashboard</h1>
  <p class="section-subtitle">Monitor your patients' medication adherence and health status</p>

  <!-- Stats Cards -->
  <div class="flex justify-between mb-2">
    <div class="card" style="border-left: 4px solid var(--primary-blue);">
      <h3>Total Patients</h3>
      <div class="flex align-center justify-between">
        <span class="big-stat">3</span> <span>👥</span>
      </div>
      <p class="text-sm">Active monitoring</p>
    </div>

    <div class="card" style="border-left: 4px solid #ff4d4d;">
      <h3>Active Alerts</h3>
      <div class="flex align-center justify-between">
        <span class="big-stat">2</span> <span>🔔</span>
      </div>
      <p class="text-sm">Require attention</p>
    </div>

    <div class="card" style="border-left: 4px solid #4CAF50;">
      <h3>Avg Adherence</h3>
      <div class="flex align-center justify-between">
        <span class="big-stat">88%</span> <span>📈</span>
      </div>
      <p class="text-sm">Across all patients</p>
    </div>

    <div class="card" style="border-left: 4px solid var(--primary-blue);">
      <h3>This Week</h3>
      <div class="flex align-center justify-between">
        <span class="big-stat">24</span> <span>⚡</span>
      </div>
      <p class="text-sm">Doses tracked</p>
    </div>
  </div>

  <!-- Recent Alerts -->
  <div class="card" style="background-color: #fdf2f2; border: 1px solid #fbbfbf;">
    <div class="flex justify-between align-center mb-2">
      <h2><span>❗</span> Recent Alerts</h2>
      <a href="${pageContext.request.contextPath}/guardian/alerts" class="btn btn-outline">View All →</a>
    </div>

    <div class="alert-item mb-2">
      <div class="flex justify-between align-center">
        <div>
          <strong>Robert Chen</strong> <span class="badge badge-high">high</span>
          <div class="text-sm">Atorvastatin 20mg</div>
          <div class="text-xs">Missed 2 hours ago</div>
        </div>
        <button class="btn btn-outline">View</button>
      </div>
    </div>

    <div class="alert-item">
      <div class="flex justify-between align-center">
        <div>
          <strong>Eleanor Rodriguez</strong> <span class="badge badge-medium">medium</span>
          <div class="text-sm">Metformin 500mg</div>
          <div class="text-xs">Missed Yesterday at 8:00 PM</div>
        </div>
        <button class="btn btn-outline">View</button>
      </div>
    </div>
  </div>

  <!-- Patient Cards -->
  <div class="flex justify-between align-center mb-2">
    <h2>Your Patients</h2>
    <a href="${pageContext.request.contextPath}/guardian/patients" class="btn btn-outline">View All Patients →</a>
  </div>

  <div class="flex justify-between">
    <!-- Patient 1 -->
<%--    <jsp:include page="/WEB-INF/views/guardian/components/patient-card.jsp">--%>
<%--      <jsp:param name="initials" value="ER"/>--%>
<%--      <jsp:param name="name" value="Eleanor Rodriguez"/>--%>
<%--      <jsp:param name="age" value="72"/>--%>
<%--      <jsp:param name="conditions" value="Hypertension, Type 2 Diabetes"/>--%>
<%--      <jsp:param name="adherence" value="92"/>--%>
<%--      <jsp:param name="updated" value="2 hours ago"/>--%>
<%--    </jsp:include>--%>

<%--    <!-- Patient 2 -->--%>
<%--    <jsp:include page="/WEB-INF/views/guardian/components/patient-card.jsp">--%>
<%--      <jsp:param name="initials" value="RC"/>--%>
<%--      <jsp:param name="name" value="Robert Chen"/>--%>
<%--      <jsp:param name="age" value="68"/>--%>
<%--      <jsp:param name="conditions" value="Heart Disease"/>--%>
<%--      <jsp:param name="adherence" value="78"/>--%>
<%--      <jsp:param name="updated" value="5 hours ago"/>--%>
<%--    </jsp:include>--%>

<%--    <!-- Patient 3 -->--%>
<%--    <jsp:include page="/WEB-INF/views/guardian/components/patient-card.jsp">--%>
<%--      <jsp:param name="initials" value="MW"/>--%>
<%--      <jsp:param name="name" value="Margaret Wilson"/>--%>
<%--      <jsp:param name="age" value="75"/>--%>
<%--      <jsp:param name="conditions" value="Arthritis, Osteoporosis"/>--%>
<%--      <jsp:param name="adherence" value="95"/>--%>
<%--      <jsp:param name="updated" value="1 hour ago"/>--%>
<%--    </jsp:include>--%>
<%--  </div>--%>
</main>
<footer class="footer">
  <p>&copy; 2025 Medora. All rights reserved.</p>
</footer>

</body>
</html>

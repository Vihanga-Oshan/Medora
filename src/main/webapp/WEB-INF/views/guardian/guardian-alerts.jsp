<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Alerts & Notifications | Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css" />
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/alerts.css" />
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/main.css" />
</head>
<body>

<jsp:include page="/WEB-INF/views/components/header-guardian.jsp"/>

<main class="container">
  <div class="flex justify-between align-center mb-2">
    <div>
      <h1 class="section-title">Alerts & Notifications</h1>
      <p class="section-subtitle">Monitor missed doses and medication alerts for your patients</p>
    </div>
    <span class="badge badge-high" style="padding: 6px 12px;">2 Unread</span>
  </div>

  <!-- Alert Summary Cards -->
  <div class="flex justify-between mb-2">
    <div class="card alert-stat red">
      <h3>Total Alerts</h3>
      <div class="stat-value">3</div>
    </div>
    <div class="card alert-stat orange">
      <h3>Unread Alerts</h3>
      <div class="stat-value">2</div>
    </div>
    <div class="card alert-stat green">
      <h3>Acknowledged</h3>
      <div class="stat-value">1</div>
    </div>
  </div>

  <!-- Alert List -->
  <div class="card">
    <h2>All Alerts</h2>
    <p class="section-subtitle">Missed medication doses and reminders</p>

    <c:forEach var="alert" items="${alerts}">
      <div class="alert-item mb-2 ${alert.bgClass}">
        <div class="flex justify-between align-center">
          <div class="flex align-center">
            <span class="alert-icon">${alert.icon}</span>
            <div>
              <strong>${alert.patient}</strong>
              <span class="badge ${alert.severityClass}">${alert.severity}</span>
              <c:if test="${alert.newAlert}"><span class="badge badge-new">New</span></c:if>
              <br/>
              <span class="text-sm">Missed Dose: ${alert.medication}</span><br/>
              <span class="text-xs">⏰ ${alert.timeAgo}</span>
            </div>
          </div>
          <div class="flex align-center">
            <c:if test="${alert.unread}">
              <button class="btn btn-outline">Mark Read</button>
            </c:if>
            <button class="btn btn-outline danger">Delete</button>
          </div>
        </div>
      </div>
    </c:forEach>
  </div>
</main>

<footer class="footer">
  <p>&copy; 2025 Medora. All rights reserved.</p>
</footer>

</body>
</html>

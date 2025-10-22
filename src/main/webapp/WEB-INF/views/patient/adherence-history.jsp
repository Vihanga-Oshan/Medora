<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adherence History - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/history.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
  <h1 class="section-title">Adherence History</h1>
  <p class="section-subtitle">Track your medication compliance over time</p>

  <div class="stats-row">
    <div class="card adherence-overall">
      <div class="card-title">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M7 14L12 9L17 14" stroke="#0096FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 18V9" stroke="#0096FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Overall Adherence Rate
      </div>
      <p class="card-subtitle">Your medication compliance score</p>
      <div class="adherence-value">${overallAdherence}%</div>
      <div class="progress-bar">
        <div class="progress-fill" style="width: ${overallAdherence}%;"></div>
      </div>
      <p class="tip">Try to improve your adherence</p>
    </div>

    <div class="card adherence-week">
      <h3 class="card-title">Last 7 Days</h3>
      <p class="card-subtitle">Daily adherence rates</p>
      <div class="day-stats">
        <c:forEach var="entry" items="${weeklyAdherence}">
          <div class="day-row">
            <span>${entry.day}</span>
            <div class="bar">
              <div class="fill" style="width: ${entry.percentage}%;"></div>
            </div>
            <span>${entry.percentage}%</span>
          </div>
        </c:forEach>
      </div>
    </div>
  </div>

  <div class="card">
    <h2 class="card-title">Medication History</h2>
    <p class="card-subtitle">Complete log of your medication intake</p>

    <c:choose>
      <c:when test="${not empty medicationHistory}">
        <ul>
          <c:forEach var="log" items="${medicationHistory}">
            <li>${log.date} - ${log.medicine} - ${log.status}</li>
          </c:forEach>
        </ul>
      </c:when>
      <c:otherwise>
        <div class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="#6c757d" stroke-width="2"/>
            <path d="M12 6V12L16 14" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>No medication history yet</p>
        </div>
      </c:otherwise>
    </c:choose>
  </div>
</main>


<jsp:include page="/WEB-INF/views/components/footer.jsp" />
</body>
</html>

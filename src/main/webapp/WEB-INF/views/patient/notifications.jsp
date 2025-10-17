<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications - Medora</title>

  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/notifications.css">
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
  <h1 class="section-title">Notifications</h1>
  <p class="section-subtitle">All caught up!</p>

  <div class="card">
    <h2 class="card-title">Medication Reminders</h2>
    <p class="card-subtitle">Your medication schedule notifications</p>

    <c:choose>
      <c:when test="${not empty notifications}">
        <ul>
          <c:forEach var="note" items="${notifications}">
            <li>${note.date} – ${note.message}</li>
          </c:forEach>
        </ul>
      </c:when>
      <c:otherwise>
        <div class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
            <path d="M12 22C12 22 16 18 16 14C16 10 13 7 12 7C11 7 8 10 8 14C8 18 12 22 12 22Z" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 14V12" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 2V6" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p>No notifications yet</p>
        </div>
      </c:otherwise>
    </c:choose>
  </div>
</main>


</body>
</html>

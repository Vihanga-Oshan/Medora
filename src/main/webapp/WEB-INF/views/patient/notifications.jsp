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

  <style>
    .notification-list {
      list-style: none;
      padding: 0;
      margin: 16px 0 0;
    }

    .notification-list li {
      position: relative;
      padding: 16px 20px;
      background-color: #f4f9fd;
      border-left: 4px solid #007dca;
      border-radius: 6px;
      margin-bottom: 12px;
      transition: background-color 0.3s ease, transform 0.2s ease;
      box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .notification-list li:hover {
      background-color: #e6f3fc;
      transform: translateY(-2px);
    }

    .notification-list .date {
      font-size: 0.85rem;
      color: #007dca;
      font-weight: 600;
      margin-bottom: 4px;
      display: block;
    }

    .notification-list .message {
      font-size: 1rem;
      color: #212529;
      font-weight: 500;
    }

    .close-btn {
      position: absolute;
      top: 12px;
      right: 16px;
      background: transparent;
      border: none;
      font-size: 18px;
      font-weight: bold;
      color: #6c757d;
      cursor: pointer;
      transition: color 0.2s;
    }

    .close-btn:hover {
      color: #dc3545;
    }

    .clear-all {
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #007dca;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .clear-all:hover {
      background-color: #005ea6;
    }

    .empty-state {
      text-align: center;
      color: #6c757d;
      padding: 40px 0;
    }
  </style>
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
  <h1 class="section-title">Notifications</h1>


  <div class="card">
    <p class="card-subtitle">Here are your recent and upcoming medication alerts:</p>

    <ul class="notification-list" id="notificationList">
      <li>
        <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        <span class="date">22 Oct 2025</span>
        <span class="message">Time to take your 8:00 AM dose of Amoxicillin (500mg).</span>
      </li>
      <li>
        <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        <span class="date">21 Oct 2025</span>
        <span class="message">You missed your 6:00 PM dose of Metformin (850mg).</span>
      </li>
      <li>
        <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        <span class="date">21 Oct 2025</span>
        <span class="message">New prescription uploaded by your doctor: "Blood Pressure Checkup".</span>
      </li>
      <li>
        <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        <span class="date">20 Oct 2025</span>
        <span class="message">Reminder: Pick up your medications from the pharmacy.</span>
      </li>
      <li>
        <button class="close-btn" onclick="this.parentElement.remove()">✕</button>
        <span class="date">19 Oct 2025</span>
        <span class="message">System update: Your prescription "Diabetes Plan 1" has been approved.</span>
      </li>
    </ul>

    <button class="clear-all" onclick="clearAllNotifications()">Clear All Notifications</button>
  </div>
</main>

<script>
  function clearAllNotifications() {
    const list = document.getElementById('notificationList');
    list.innerHTML = `
      <div class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
          <path d="M12 22C12 22 16 18 16 14C16 10 13 7 12 7C11 7 8 10 8 14C8 18 12 22 12 22Z" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 14V12" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 2V6" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>No notifications yet</p>
      </div>`;
  }
</script>
<jsp:include page="/WEB-INF/views/components/footer.jsp" />
</body>
</html>

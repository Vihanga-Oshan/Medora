<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
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
            <c:choose>
              <c:when test="${not empty notifications}">
                <c:forEach var="n" items="${notifications}">
                  <li id="notification-${n.id}" class="${n.read ? 'read' : 'unread'}">
                    <button class="close-btn" onclick="deleteNotification(${n.id})">✕</button>
                    <span class="date">${n.formattedDate}</span>
                    <span class="message">${n.message}</span>
                  </li>
                </c:forEach>
              </c:when>
              <c:otherwise>
                <div class="empty-state">
                  <p>No new notifications.</p>
                </div>
              </c:otherwise>
            </c:choose>
          </ul>

          <button class="clear-all" onclick="clearAllNotifications()">Clear All Notifications</button>
        </div>
      </main>

      <script>
        function deleteNotification(id) {
          if (!confirm('Are you sure you want to delete this notification?')) return;

          fetch('${pageContext.request.contextPath}/patient/notifications/action?action=delete&id=' + id, {
            method: 'POST'
          }).then(res => {
            if (res.ok) {
              const item = document.getElementById('notification-' + id);
              if (item) item.remove();
              checkEmpty();
            } else {
              alert('Failed to delete notification.');
            }
          });
        }

        function clearAllNotifications() {
          if (!confirm('Are you sure you want to clear all notifications?')) return;

          fetch('${pageContext.request.contextPath}/patient/notifications/action?action=clearAll', {
            method: 'POST'
          }).then(res => {
            if (res.ok) {
              document.getElementById('notificationList').innerHTML = `
          <div class="empty-state">
            <p>No notifications yet</p>
          </div>`;
            } else {
              alert('Failed to clear notifications.');
            }
          });
        }

        function checkEmpty() {
          const list = document.getElementById('notificationList');
          if (list.children.length === 0) {
            list.innerHTML = `
        <div class="empty-state">
          <p>No notifications yet</p>
        </div>`;
          }
        }
      </script>
      <jsp:include page="/WEB-INF/views/components/footer.jsp" />
    </body>

    </html>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Guardian Dashboard | Medora</title>

      <!-- CSS -->
      <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/dashboard.css" />
      <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css" />
      <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/main.css" />

      <style>
        /* CRITICAL INLINE CSS TO ENSURE DASHBOARD LOADS CORRECTLY (Copied from Patient Dashboard) */
        :root {
          /* Color Palette */
          --navy-dark: #0f172a;
          --navy-light: #1e293b;
          --medical-blue: #0078c3;
          --medical-blue-hover: #0066a5;
          --accent-orange: #ef4444;
          --accent-orange-hover: #dc2626;

          --success-green: #10b981;
          --warning-yellow: #f59e0b;
          --danger-red: #ef4444;

          --bg-body: #f8fafc;
          --bg-card: #ffffff;
          --border-color: #e2e8f0;

          --text-main: #334155;
          --text-muted: #64748b;

          --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
          --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
          --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

          --radius-md: 0.5rem;
          --radius-lg: 0.75rem;
        }

        body {
          background-color: var(--bg-body);
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          color: var(--text-main);
          margin: 0;
          padding: 0;
          padding-top: 0 !important;
          box-sizing: border-box;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
          color: var(--navy-dark);
        }

        .dashboard-wrapper {
          min-height: 100vh;
          display: flex;
          flex-direction: column;
        }

        .main-layout {
          max-width: 1200px;
          margin: -60px auto 40px;
          padding: 0 20px;
          z-index: 10;
          position: relative;
          width: 100%;
        }

        /* Hero Section */
        .dashboard-hero {
          position: relative;
          background-color: var(--medical-blue);
          padding: 120px 20px 100px;
          color: white;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          gap: 20px;
          overflow: hidden;
        }

        .dashboard-hero::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-image: url('${pageContext.request.contextPath}/assets/hero-image.jpg');
          background-size: cover;
          background-position: center 30%;
          filter: blur(3px);
          opacity: 0.5;
          z-index: 0;
        }

        .dashboard-hero::after {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(135deg, rgba(0, 120, 195, 0.75) 0%, rgba(0, 74, 124, 0.6) 100%);
          z-index: 1;
        }

        .hero-content,
        .hero-actions {
          position: relative;
          z-index: 2;
        }

        .hero-content {
          max-width: 600px;
        }

        .hero-title {
          font-size: 2.2rem;
          font-weight: 600;
          margin-bottom: 8px;
          letter-spacing: -0.5px;
          color: white;
        }

        .highlight-text {
          color: white;
          font-weight: 800;
          text-decoration: underline;
          text-underline-offset: 4px;
        }

        .hero-subtitle {
          font-size: 1.1rem;
          color: rgba(255, 255, 255, 0.9);
          font-weight: 400;
        }

        .hero-actions {
          display: flex;
          gap: 15px;
        }

        .btn {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 12px 24px;
          border-radius: var(--radius-md);
          font-weight: 500;
          text-decoration: none;
          transition: all 0.2s ease;
          font-size: 0.95rem;
          border: none;
          cursor: pointer;
        }

        .dashboard-hero .btn-primary {
          background-color: var(--medical-blue);
          color: white !important;
          border: 1px solid rgba(255, 255, 255, 0.4);
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-hero .btn-primary:hover {
          background-color: var(--medical-blue-hover);
          transform: translateY(-2px);
          border-color: rgba(255, 255, 255, 0.6);
        }

        .dashboard-hero .btn-outline {
          background-color: rgba(255, 255, 255, 0.15);
          backdrop-filter: blur(8px);
          color: white;
          border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .dashboard-hero .btn-outline:hover {
          background-color: rgba(255, 255, 255, 0.25);
          border-color: rgba(255, 255, 255, 0.6);
        }

        /* Stats Overview */
        .stats-overview {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
          gap: 20px;
          margin-bottom: 30px;
        }

        .stat-card {
          background: var(--bg-card);
          border-radius: var(--radius-lg);
          padding: 24px;
          box-shadow: var(--shadow-md);
          display: flex;
          align-items: center;
          gap: 20px;
          border: 1px solid var(--border-color);
          transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
          transform: translateY(-4px);
          box-shadow: var(--shadow-lg);
        }

        .stat-icon-wrapper {
          width: 50px;
          height: 50px;
          border-radius: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.25rem;
          flex-shrink: 0;
        }

        .bg-blue {
          background-color: #e0f2fe;
          color: var(--medical-blue);
        }

        .bg-green {
          background-color: #dcfce7;
          color: var(--success-green);
        }

        .bg-yellow {
          background-color: #fef3c7;
          color: var(--warning-yellow);
        }

        .bg-red {
          background-color: #fee2e2;
          color: var(--danger-red);
        }

        .stat-details {
          display: flex;
          flex-direction: column;
        }

        .stat-label {
          font-size: 0.85rem;
          color: var(--text-muted);
          font-weight: 500;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }

        .stat-number {
          font-size: 1.75rem;
          font-weight: 700;
          color: var(--navy-dark);
          margin: 0;
          line-height: 1.2;
        }

        /* Content Grid */
        .content-grid {
          display: grid;
          grid-template-columns: 2fr 1fr;
          gap: 30px;
        }

        @media (max-width: 900px) {
          .content-grid {
            grid-template-columns: 1fr;
          }
        }

        /* Schedule/List Column */
        .section-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
        }

        .section-header h2 {
          font-size: 1.5rem;
          margin: 0;
        }

        .schedule-list {
          display: flex;
          flex-direction: column;
          gap: 15px;
        }

        .medication-item {
          background: var(--bg-card);
          border-radius: var(--radius-md);
          padding: 20px;
          box-shadow: var(--shadow-sm);
          display: flex;
          align-items: center;
          border-left: 4px solid var(--medical-blue);
          gap: 20px;
        }

        .med-info-block {
          flex-grow: 1;
        }

        .med-info-block h3 {
          margin: 0 0 6px 0;
          font-size: 1.15rem;
        }

        .instructions {
          margin: 2px 0;
          color: var(--text-muted);
          font-size: 0.9rem;
        }

        .action-btn {
          padding: 8px 16px;
          border-radius: var(--radius-md);
          background: var(--bg-body);
          border: 1px solid var(--border-color);
          color: var(--text-main);
          cursor: pointer;
          text-decoration: none;
          font-size: 0.9rem;
        }

        .action-btn:hover {
          background: #f1f5f9;
        }

        /* Right Column Panels */
        .card-panel {
          background: var(--bg-card);
          border-radius: var(--radius-lg);
          box-shadow: var(--shadow-sm);
          padding: 25px;
          height: fit-content;
          border: 1px solid var(--border-color);
          margin-bottom: 20px;
        }

        .card-panel h2 {
          font-size: 1.25rem;
          margin: 0 0 20px 0;
          border-bottom: 2px solid #f1f5f9;
          padding-bottom: 15px;
        }

        .notification-list-widget {
          list-style: none;
          padding: 0;
          margin: 0;
        }

        .notification-item {
          padding: 12px 0;
          border-bottom: 1px solid #f1f5f9;
        }

        .notification-item:last-child {
          border-bottom: none;
        }

        .notif-header {
          display: flex;
          justify-content: space-between;
          font-size: 0.8rem;
          color: var(--text-muted);
          margin-bottom: 4px;
        }

        .notif-msg {
          margin: 0;
          font-size: 0.95rem;
          line-height: 1.4;
          color: var(--navy-dark);
        }

        /* Modern footer spacing */
        footer {
          margin-top: auto;
        }
      </style>
    </head>

    <body>

      <jsp:include page="/WEB-INF/views/components/header-guardian.jsp" />

      <div class="dashboard-wrapper">

        <!-- ===== Hero Section ===== -->
        <header class="dashboard-hero">
          <div class="hero-content">
            <h1 class="hero-title">Welcome back, <span class="highlight-text">${guardianName}</span></h1>
            <p class="hero-subtitle">Monitor your patients' medication adherence and health status.</p>
          </div>
          <div class="hero-actions">
            <a href="${pageContext.request.contextPath}/guardian/patients" class="btn btn-primary">
              &#128101; View Patients
            </a>
            <a href="${pageContext.request.contextPath}/guardian/alerts" class="btn btn-outline">
              &#128276; Active Alerts
            </a>
          </div>
        </header>

        <div class="container main-layout">

          <!-- Toast Notification Container -->
          <div id="toast-container" style="position: fixed; top: 100px; right: 20px; z-index: 9999;">
            <c:if test="${not empty sessionScope.successMessage}">
              <div class="toast-notification success"
                style="background: #dcfce7; color: #15803d; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 10px; display: flex; align-items: center; gap: 10px; border-left: 5px solid #16a34a; animation: slideIn 0.5s ease-out;">
                <span>&#9989;</span>
                <span>${sessionScope.successMessage}</span>
              </div>
              <c:remove var="successMessage" scope="session" />
            </c:if>
            <c:if test="${not empty sessionScope.infoMessage}">
              <div class="toast-notification info"
                style="background: #e0f2fe; color: #0369a1; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 10px; display: flex; align-items: center; gap: 10px; border-left: 5px solid #0284c7; animation: slideIn 0.5s ease-out;">
                <span>&#8505;</span>
                <span>${sessionScope.infoMessage}</span>
              </div>
              <c:remove var="infoMessage" scope="session" />
            </c:if>
            <c:if test="${not empty sessionScope.errorMessage}">
              <div class="toast-notification error"
                style="background: #fee2e2; color: #b91c1c; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 10px; display: flex; align-items: center; gap: 10px; border-left: 5px solid #dc2626; animation: slideIn 0.5s ease-out;">
                <span>&#9888;</span>
                <span>${sessionScope.errorMessage}</span>
              </div>
              <c:remove var="errorMessage" scope="session" />
            </c:if>
          </div>

          <style>
            @keyframes slideIn {
              from {
                transform: translateX(100%);
                opacity: 0;
              }

              to {
                transform: translateX(0);
                opacity: 1;
              }
            }

            @keyframes fadeOut {
              from {
                opacity: 1;
              }

              to {
                opacity: 0;
              }
            }
          </style>

          <script>
            // Auto-dismiss toasts after 3 seconds
            document.addEventListener('DOMContentLoaded', function () {
              const toasts = document.querySelectorAll('.toast-notification');
              toasts.forEach(toast => {
                setTimeout(() => {
                  toast.style.animation = 'fadeOut 0.5s ease-out forwards';
                  setTimeout(() => {
                    toast.remove();
                  }, 500); // Wait for fadeOut animation
                }, 3000);
              });
            });
          </script>

          <!-- ===== Stats Overview ===== -->
          <section class="stats-overview">
            <!-- Total Patients -->
            <div class="stat-card">
              <div class="stat-icon-wrapper bg-blue">
                &#128100;
              </div>
              <div class="stat-details">
                <span class="stat-label">Total Patients</span>
                <h3 class="stat-number">${totalPatients}</h3>
              </div>
            </div>

            <!-- Active Alerts -->
            <div class="stat-card">
              <div class="stat-icon-wrapper bg-red">
                &#9940;
              </div>
              <div class="stat-details">
                <span class="stat-label">Active Alerts</span>
                <h3 class="stat-number">${activeAlertsCount}</h3>
              </div>
            </div>

            <!-- Avg Adherence -->
            <div class="stat-card">
              <div class="stat-icon-wrapper bg-green">
                &#128200;
              </div>
              <div class="stat-details">
                <span class="stat-label">Avg Adherence</span>
                <h3 class="stat-number">${avgAdherence}%</h3>
              </div>
            </div>
          </section>

          <!-- ===== Content Grid ===== -->
          <div class="content-grid">

            <!-- Left Column: Patient List -->
            <div class="schedule-column">
              <div class="section-header">
                <h2>Your Patients</h2>
              </div>

              <div class="schedule-list">
                <c:choose>
                  <c:when test="${not empty patients}">
                    <c:forEach var="patient" items="${patients}">
                      <div class="medication-item">
                        <div class="stat-icon-wrapper bg-blue" style="margin-right: 10px;">
                          &#128100;
                        </div>
                        <div class="med-info-block">
                          <h3>${patient.name}</h3>
                        </div>
                        <div class="med-actions-block" style="display: flex; gap: 10px; align-items: center;">
                          <a href="${pageContext.request.contextPath}/guardian/patients?nic=${patient.nic}"
                            class="action-btn">View Profile</a>

                          <form action="${pageContext.request.contextPath}/guardian/remove-patient" method="post"
                            style="margin: 0;"
                            onsubmit="return confirm('Are you sure you want to remove this patient?');">
                            <input type="hidden" name="patientNic" value="${patient.nic}">
                            <button type="submit" class="action-btn"
                              style="background: #fee2e2; color: #b91c1c; border-color: #fecaca;">Remove</button>
                          </form>
                        </div>
                      </div>
                    </c:forEach>
                  </c:when>
                  <c:otherwise>
                    <p>No patients linked to your account.</p>
                  </c:otherwise>
                </c:choose>
              </div>
            </div>

            <!-- Right Column: Recent Alerts -->
            <div class="timetable-column">
              <div class="card-panel">
                <h2>Recent Alerts</h2>

                <ul class="notification-list-widget">
                  <c:choose>
                    <c:when test="${not empty recentAlerts}">
                      <c:forEach var="alert" items="${recentAlerts}">
                        <li class="notification-item">
                          <div class="notif-header">
                            <span
                              style="color: ${alert.type == 'Critical' ? 'var(--danger-red)' : 'var(--warning-yellow)'}; font-weight: bold;">
                              ${alert.type == 'Critical' ? '&#9888;' : '&#8505;'} ${alert.type}
                            </span>
                            <span>${alert.date.toLocalDate()}</span>
                          </div>
                          <p class="notif-msg">${alert.message}</p>
                        </li>
                      </c:forEach>
                    </c:when>
                    <c:otherwise>
                      <p>No recent alerts.</p>
                    </c:otherwise>
                  </c:choose>
                </ul>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Footer -->
      <jsp:include page="/WEB-INF/views/components/footer.jsp" />

    </body>

    </html>
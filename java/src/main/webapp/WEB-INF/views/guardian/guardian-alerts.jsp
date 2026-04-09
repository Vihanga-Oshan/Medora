<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Alerts & Notifications | Medora</title>

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

        .btn-primary {
          background-color: var(--medical-blue);
          color: white !important;
          box-shadow: 0 4px 6px rgba(0, 120, 195, 0.2);
        }

        .btn-primary:hover {
          background-color: var(--medical-blue-hover);
          transform: translateY(-2px);
        }

        .dashboard-hero .btn-primary {
          background-color: var(--medical-blue);
          /* Default Blue */
          color: white !important;
          border: 1px solid rgba(255, 255, 255, 0.4);
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-hero .btn-primary {
          background-color: var(--medical-blue);
          /* Default Blue */
          color: white !important;
          border: 1px solid rgba(255, 255, 255, 0.4);
          /* Border for visibility */
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-hero .btn-primary:hover {
          background-color: var(--medical-blue-hover);
          transform: translateY(-2px);
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

        /* Alert Items */
        .alert-container {
          display: flex;
          flex-direction: column;
          gap: 15px;
        }

        .alert-item {
          background: var(--bg-card);
          padding: 20px;
          border-radius: var(--radius-md);
          box-shadow: var(--shadow-sm);
          border: 1px solid var(--border-color);
          display: flex;
          justify-content: space-between;
          align-items: center;
          transition: transform 0.2s;
        }

        .alert-item:hover {
          transform: translateY(-2px);
          box-shadow: var(--shadow-md);
        }

        .alert-item.unread {
          border-left: 4px solid var(--medical-blue);
          background: #ffffff;
        }

        .alert-content {
          display: flex;
          gap: 15px;
          align-items: start;
        }

        .alert-icon {
          font-size: 1.5rem;
        }

        .alert-text h4 {
          margin: 0 0 5px 0;
          font-size: 1rem;
          color: var(--navy-dark);
        }

        .alert-text p {
          margin: 0;
          font-size: 0.9rem;
          color: var(--text-muted);
        }

        .alert-time {
          font-size: 0.8rem;
          color: var(--text-muted);
          margin-top: 5px;
          display: block;
        }

        .alert-actions {
          display: flex;
          gap: 10px;
        }

        .badge {
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 0.75rem;
          font-weight: bold;
          text-transform: uppercase;
        }

        .badge-high {
          background: #fee2e2;
          color: #ef4444;
        }

        .badge-medium {
          background: #fef3c7;
          color: #92400e;
        }

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
            <h1 class="hero-title">Alerts & Notifications</h1>
            <p class="hero-subtitle">Monitor missed doses and medication alerts for your patients.</p>
          </div>
          <div class="hero-actions">
            <form action="${pageContext.request.contextPath}/guardian/alerts" method="post" style="margin: 0;">
              <input type="hidden" name="action" value="markAllRead">
              <button type="submit" class="btn btn-primary"
                style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); color: white !important; border: 1px solid rgba(255,255,255,0.3);">
                &#10003; Mark All Read
              </button>
            </form>
          </div>
        </header>

        <div class="container main-layout">

          <!-- Stats Overview -->
          <section class="stats-overview">
            <div class="stat-card">
              <div class="stat-icon-wrapper bg-blue">
                &#9888;
              </div>
              <div class="stat-details">
                <span class="stat-label">Total Alerts</span>
                <h3 class="stat-number">${totalAlerts}</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon-wrapper bg-yellow">
                &#128276;
              </div>
              <div class="stat-details">
                <span class="stat-label">Unread</span>
                <h3 class="stat-number">${unreadAlerts}</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon-wrapper bg-green">
                &#9989;
              </div>
              <div class="stat-details">
                <span class="stat-label">Resolved</span>
                <h3 class="stat-number">${resolvedAlerts}</h3>
              </div>
            </div>
          </section>

          <!-- Main Content -->
          <div class="card-panel"
            style="background: var(--bg-card); border-radius: var(--radius-lg); padding: 25px; border: 1px solid var(--border-color);">
            <h2 style="font-size: 1.25rem; margin: 0 0 20px 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
              Recent Alerts</h2>

            <div class="alert-container">
              <c:choose>
                <c:when test="${not empty notifications}">
                  <c:forEach var="n" items="${notifications}">
                    <div class="alert-item ${n.read ? '' : 'unread'}">
                      <div class="alert-content">
                        <div class="alert-icon"
                          style="color: ${n.type == 'Critical' ? 'var(--danger-red)' : (n.type == 'Info' ? 'var(--medical-blue)' : 'var(--accent-orange)')};">
                          ${n.type == 'Critical' ? '&#9888;' : (n.type == 'Info' ? '&#8505;' : '&#9888;')}
                        </div>
                        <div class="alert-text">
                          <h4>${n.type} <span
                              class="badge ${n.type == 'Critical' ? 'badge-high' : 'badge-medium'}">${n.type}
                              Priority</span></h4>
                          <p><strong>${patientMap[n.patientNic].name}</strong>: ${n.message}</p>
                          <span class="alert-time">${n.date.toLocalDate()} ${n.date.toLocalTime()}</span>
                        </div>
                      </div>
                      <div class="alert-actions">
                        <c:if test="${!n.read}">
                          <form action="${pageContext.request.contextPath}/guardian/alerts" method="post"
                            style="margin: 0;">
                            <input type="hidden" name="action" value="markAsRead">
                            <input type="hidden" name="id" value="${n.id}">
                            <button type="submit" class="btn"
                              style="padding: 6px 12px; font-size: 0.85rem; border: 1px solid var(--border-color); background: white;">Dismiss</button>
                          </form>
                        </c:if>
                        <a href="tel:${patientMap[n.patientNic].phone}" class="btn btn-primary"
                          style="padding: 6px 12px; font-size: 0.85rem; background: var(--medical-blue); color: white !important; text-decoration: none;">Call
                          Patient</a>
                      </div>
                    </div>
                  </c:forEach>
                </c:when>
                <c:otherwise>
                  <p style="padding: 20px; text-align: center; color: var(--text-muted);">No alerts found.</p>
                </c:otherwise>
              </c:choose>
            </div>
          </div>
        </div>
      </div>

      <jsp:include page="/WEB-INF/views/components/footer.jsp" />

    </body>

    </html>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Adherence Reports | Medora</title>

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

        footer {
          margin-top: auto;
        }

        /* Specific to Reports */
        .report-summary-item {
          display: flex;
          justify-content: space-between;
          padding: 12px 0;
          border-bottom: 1px solid #f1f5f9;
        }

        .report-summary-item:last-child {
          border-bottom: none;
        }

        .highlight {
          font-weight: bold;
          color: var(--success-green);
          font-size: 1.1rem;
        }
      </style>
    </head>

    <body>

      <jsp:include page="/WEB-INF/views/components/header-guardian.jsp" />

      <div class="dashboard-wrapper">

        <!-- ===== Hero Section ===== -->
        <header class="dashboard-hero">
          <div class="hero-content">
            <h1 class="hero-title">Adherence Reports</h1>
            <p class="hero-subtitle">View and analyze medication adherence trends and statistics.</p>
          </div>
          <div class="hero-actions">
            <button class="btn btn-primary">
              &#128229; Export Report
            </button>
          </div>
        </header>

        <div class="container main-layout">

          <!-- Patient Selector & Stats -->
          <div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
            <form action="" method="get">
              <select name="nic" onchange="this.form.submit()"
                style="padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.9rem;">
                <c:forEach var="p" items="${patients}">
                  <option value="${p.nic}" ${p.nic==selectedPatient.nic ? 'selected' : '' }>${p.name}</option>
                </c:forEach>
                <c:if test="${empty patients}">
                  <option>No patients found</option>
                </c:if>
              </select>
            </form>
          </div>

          <!-- Stats Overview -->
          <section class="stats-overview">
            <div class="stat-card">
              <div class="stat-icon-wrapper bg-blue">
                &#128200;
              </div>
              <div class="stat-details">
                <span class="stat-label">Avg Adherence</span>
                <h3 class="stat-number">${overallAdherence}%</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon-wrapper bg-green">
                &#10003;
              </div>
              <div class="stat-details">
                <span class="stat-label">Doses Taken</span>
                <h3 class="stat-number">${adherenceStats.taken}</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon-wrapper bg-red">
                &#10060;
              </div>
              <div class="stat-details">
                <span class="stat-label">Doses Missed</span>
                <h3 class="stat-number">${adherenceStats.missed}</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon-wrapper bg-yellow">
                &#128198;
              </div>
              <div class="stat-details">
                <span class="stat-label">Recorded Doses</span>
                <h3 class="stat-number">${adherenceStats.total}</h3>
              </div>
            </div>
          </section>

          <!-- Main Content -->
          <div class="content-grid">

            <!-- Left: Chart & Trends -->
            <div class="card-panel">
              <h2>Adherence Trend (Last 7 Days)</h2>
              <div
                style="height: 300px; display: flex; align-items: flex-end; justify-content: space-around; padding-top: 20px;">
                <c:choose>
                  <c:when test="${not empty weeklyAdherence}">
                    <c:forEach var="day" items="${weeklyAdherence}">
                      <div style="display: flex; flex-direction: column; align-items: center; width: 10%;">
                        <div
                          style="width: 100%; background: #e2e8f0; border-radius: 4px; height: 200px; position: relative; display: flex; align-items: flex-end;">
                          <div
                            style="width: 100%; background: var(--medical-blue); border-radius: 4px; height: ${day.percentage}%; transition: height 0.5s;">
                          </div>
                        </div>
                        <span style="margin-top: 10px; font-size: 0.8rem; color: var(--text-muted);">${day.day}</span>
                        <span style="font-size: 0.75rem; font-weight: bold;">${day.percentage}%</span>
                      </div>
                    </c:forEach>
                  </c:when>
                  <c:otherwise>
                    <p style="color: var(--text-muted);">No data available for chart.</p>
                  </c:otherwise>
                </c:choose>
              </div>
            </div>

            <!-- Right: Summary -->
            <div class="card-panel">
              <h2>Report Summary</h2>

              <div
                style="margin-bottom: 20px; padding: 15px; background: #e0f2fe; border-radius: var(--radius-md); color: #0078c3;">
                <strong>Patient:</strong> ${selectedPatient.name}
              </div>

              <div class="report-summary-item">
                <span>Overall Score</span>
                <span class="highlight">${overallAdherence}%</span>
              </div>
              <div class="report-summary-item">
                <span>Condition</span>
                <span>${selectedPatient.chronicIssues}</span>
              </div>
              <div class="report-summary-item">
                <span>Contact</span>
                <span>${selectedPatient.phone}</span>
              </div>

              <button class="btn btn-primary"
                style="width: 100%; margin-top: 20px; justify-content: center; background: var(--medical-blue); color: white;">
                Download PDF
              </button>
            </div>
          </div>

        </div>
      </div>

      <jsp:include page="/WEB-INF/views/components/footer.jsp" />

    </body>

    </html>
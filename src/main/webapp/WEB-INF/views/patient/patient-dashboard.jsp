<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
    <%@ page contentType="text/html;charset=UTF-8" %>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Patient Dashboard | Medora</title>


            <!-- CSS -->
            <link rel="stylesheet"
                href="${pageContext.request.contextPath}/css/patient/main.css?v=<%= System.currentTimeMillis() %>">
            <link rel="stylesheet"
                href="${pageContext.request.contextPath}/css/patient/dashboard.css?v=<%= System.currentTimeMillis() %>">

            <style>
                /* CRITICAL INLINE CSS TO ENSURE DASHBOARD LOADS CORRECTLY */
                :root {
                    /* Color Palette */
                    --navy-dark: #0f172a;
                    --navy-light: #1e293b;
                    --medical-blue: #007acc;
                    --medical-blue-hover: #005999;
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

                /* Layout Containers */
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
                    background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-light) 100%);
                    padding: 60px 20px 100px;
                    color: white;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 20px;
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
                    color: var(--medical-blue);
                    font-weight: 700;
                }

                .hero-subtitle {
                    font-size: 1.1rem;
                    color: #94a3b8;
                    font-weight: 300;
                }

                .hero-actions {
                    display: flex;
                    gap: 15px;
                }

                /* Buttons */
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
                }

                .btn-primary {
                    background-color: var(--medical-blue);
                    color: white;
                    box-shadow: 0 4px 6px rgba(0, 125, 202, 0.2);
                }

                .btn-primary:hover {
                    background-color: var(--medical-blue-hover);
                    transform: translateY(-2px);
                }

                .btn-outline {
                    background-color: rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(4px);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                }

                .btn-outline:hover {
                    background-color: rgba(255, 255, 255, 0.2);
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

                /* Schedule Column */
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

                .date-badge {
                    background: #e2e8f0;
                    color: var(--text-main);
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 0.85rem;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 8px;
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
                    transition: background 0.2s;
                }

                .medication-item:hover {
                    background-color: #fdfdfd;
                }

                .med-time {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    min-width: 80px;
                    text-align: center;
                    padding-right: 20px;
                    border-right: 1px solid var(--border-color);
                }

                .time-slot {
                    font-size: 1.1rem;
                    font-weight: 700;
                    color: var(--navy-dark);
                }

                .meal-timing {
                    font-size: 0.75rem;
                    color: var(--text-muted);
                    margin-top: 4px;
                    background: #f1f5f9;
                    padding: 2px 8px;
                    border-radius: 4px;
                }

                .med-info-block {
                    flex-grow: 1;
                }

                .med-info-block h3 {
                    margin: 0 0 6px 0;
                    font-size: 1.15rem;
                }

                .dosage-info,
                .instructions {
                    margin: 2px 0;
                    color: var(--text-muted);
                    font-size: 0.9rem;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .dosage-info i,
                .instructions i {
                    color: var(--medical-blue);
                    width: 16px;
                    text-align: center;
                }

                .med-actions-block {
                    display: flex;
                    gap: 10px;
                }

                .action-btn {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.1rem;
                    transition: transform 0.2s, background 0.2s;
                    background: var(--bg-body);
                }

                .btn-check {
                    color: var(--success-green);
                    border: 1px solid #bbf7d0;
                    background: #f0fdf4;
                }

                .btn-check:hover {
                    background: var(--success-green);
                    color: white;
                    transform: scale(1.1);
                }

                .btn-cross {
                    color: var(--danger-red);
                    border: 1px solid #fecaca;
                    background: #fef2f2;
                }

                .btn-cross:hover {
                    background: var(--danger-red);
                    color: white;
                    transform: scale(1.1);
                }

                .empty-state {
                    text-align: center;
                    padding: 40px;
                    background: white;
                    border-radius: var(--radius-md);
                    color: var(--text-muted);
                }

                .empty-state i {
                    font-size: 3rem;
                    color: #cbd5e1;
                    margin-bottom: 10px;
                }

                /* Timetable Column */
                .card-panel {
                    background: var(--bg-card);
                    border-radius: var(--radius-lg);
                    box-shadow: var(--shadow-sm);
                    padding: 25px;
                    height: fit-content;
                    border: 1px solid var(--border-color);
                }

                .card-panel h2 {
                    font-size: 1.25rem;
                    margin: 0 0 20px 0;
                    border-bottom: 2px solid #f1f5f9;
                    padding-bottom: 15px;
                }

                .modern-date-selector {
                    margin-bottom: 25px;
                }

                .modern-date-selector label {
                    display: block;
                    font-size: 0.85rem;
                    margin-bottom: 8px;
                    color: var(--text-muted);
                    font-weight: 500;
                }

                .input-group {
                    display: flex;
                    gap: 8px;
                }

                .input-group input {
                    flex-grow: 1;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: var(--radius-md);
                    font-family: inherit;
                    color: var(--navy-dark);
                }

                .btn-icon {
                    background: var(--medical-blue);
                    color: white;
                    border: none;
                    border-radius: var(--radius-md);
                    width: 42px;
                    cursor: pointer;
                    transition: background 0.2s;
                }

                .btn-icon:hover {
                    background: var(--medical-blue-hover);
                }

                .timetable-scroll {
                    max-height: 400px;
                    overflow-y: auto;
                    padding-right: 5px;
                }

                .timetable-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px 0;
                    border-bottom: 1px solid #f1f5f9;
                }

                .timetable-row:last-child {
                    border-bottom: none;
                }

                .row-info h4 {
                    margin: 0 0 4px 0;
                    font-size: 0.95rem;
                    font-weight: 600;
                }

                .row-info span {
                    font-size: 0.8rem;
                    color: var(--text-muted);
                }

                .status-pill {
                    font-size: 0.7rem;
                    font-weight: 700;
                    padding: 4px 8px;
                    border-radius: 12px;
                    text-transform: uppercase;
                }

                .status-pending {
                    background: #fef3c7;
                    color: #b45309;
                }

                .status-taken {
                    background: #dcfce7;
                    color: #15803d;
                }

                .status-missed {
                    background: #fee2e2;
                    color: #b91c1c;
                }
            </style>
        </head>

        <body>

            <!-- Include top navigation -->
            <%@ include file="/WEB-INF/views/components/header.jsp" %>

                <div class="dashboard-wrapper">

                    <!-- ===== Hero Section ===== -->
                    <header class="dashboard-hero">
                        <div class="hero-content">
                            <h1 class="hero-title">Welcome back, <span class="highlight-text">${patient.name}</span>
                            </h1>
                            <p class="hero-subtitle">Manage your health with precision and ease.</p>
                        </div>
                        <div class="hero-actions">
                            <a href="${pageContext.request.contextPath}/router/shop" class="btn btn-primary">
                                &#128722; Order Medicines
                            </a>
                            <a href="${pageContext.request.contextPath}/router/shop/orders" class="btn btn-outline">
                                &#128230; My Orders
                            </a>
                        </div>
                    </header>

                    <div class="container main-layout">

                        <!-- ===== Stats Overview ===== -->
                        <section class="stats-overview">
                            <div class="stat-card">
                                <div class="stat-icon-wrapper bg-blue">
                                    &#128138;
                                </div>
                                <div class="stat-details">
                                    <span class="stat-label">Total Doses</span>
                                    <h3 class="stat-number">${totalCount}</h3>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon-wrapper bg-green">
                                    &#9989;
                                </div>
                                <div class="stat-details">
                                    <span class="stat-label">Taken</span>
                                    <h3 class="stat-number">${takenCount}</h3>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon-wrapper bg-yellow">
                                    &#8987;
                                </div>
                                <div class="stat-details">
                                    <span class="stat-label">Pending</span>
                                    <h3 class="stat-number">${pendingCount}</h3>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon-wrapper bg-red">
                                    &#9888;
                                </div>
                                <div class="stat-details">
                                    <span class="stat-label">Missed</span>
                                    <h3 class="stat-number">${missedCount}</h3>
                                </div>
                            </div>
                        </section>

                        <!-- ===== Content Grid ===== -->
                        <div class="content-grid">

                            <!-- Left Column: Adherence & Today's Schedule -->
                            <div class="schedule-column">

                                <!-- Adherence Widget (New) -->
                                <div class="card-panel"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="adherence-text">
                                        <h3>Adherence Score</h3>
                                        <p>Based on today's schedule</p>
                                    </div>
                                    <div class="adherence-widget">
                                        <div class="adherence-chart"
                                            style="--adherence-deg: ${adherenceScore * 3.6}deg;">
                                            <span class="adherence-score">${adherenceScore}%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-header">
                                    <h2>Today's Schedule</h2>
                                    <div class="date-badge">
                                        &#128197;
                                        <span>Today</span>
                                    </div>
                                </div>

                                <div class="schedule-list">
                                    <c:choose>
                                        <c:when test="${empty pendingMedications}">
                                            <div class="empty-state-modern"
                                                style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
                                                <div class="state-icon"
                                                    style="background:#dcfce7; width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                                    <span style="font-size:24px; color:#10b981;">&#10003;</span>
                                                </div>
                                                <h3 style="margin: 0 0 8px 0; color: var(--navy-dark);">All Caught Up!
                                                </h3>
                                                <p style="margin: 0; color: var(--text-muted);">You have no pending
                                                    medications for the rest of today.</p>
                                            </div>
                                        </c:when>
                                        <c:otherwise>
                                            <c:forEach var="m" items="${pendingMedications}">
                                                <div class="medication-item">
                                                    <div class="med-time">
                                                        <span class="time-slot">${m.frequency}</span>
                                                        <span class="meal-timing">${m.mealTiming}</span>
                                                    </div>
                                                    <div class="med-info-block">
                                                        <h3>${m.medicineName}</h3>
                                                        <p class="dosage-info"><span>&#128138;</span> ${m.dosage}
                                                        </p>
                                                        <p class="instructions"><span>&#8505;</span>
                                                            ${m.instructions}</p>
                                                    </div>
                                                    <div class="med-actions-block">
                                                        <form
                                                            action="${pageContext.request.contextPath}/patient/mark-medication"
                                                            method="post">
                                                            <input type="hidden" name="scheduleId" value="${m.id}" />
                                                            <input type="hidden" name="patientNic"
                                                                value="${sessionScope.patient.nic}" />
                                                            <input type="hidden" name="status" value="TAKEN" />
                                                            <input type="hidden" name="timeSlot"
                                                                value="${m.frequency}" />
                                                            <button type="submit" class="action-btn btn-check"
                                                                title="Mark as Taken">
                                                                &#10003;
                                                            </button>
                                                        </form>
                                                        <form
                                                            action="${pageContext.request.contextPath}/patient/mark-medication"
                                                            method="post">
                                                            <input type="hidden" name="scheduleId" value="${m.id}" />
                                                            <input type="hidden" name="patientNic"
                                                                value="${sessionScope.patient.nic}" />
                                                            <input type="hidden" name="status" value="MISSED" />
                                                            <input type="hidden" name="timeSlot"
                                                                value="${m.frequency}" />
                                                            <button type="submit" class="action-btn btn-cross"
                                                                title="Mark as Missed">
                                                                &#10007;
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </c:forEach>
                                        </c:otherwise>
                                    </c:choose>
                                </div>
                            </div>

                            <!-- Right Column: Timetable & Notifications -->
                            <div class="timetable-column">
                                <div class="card-panel">
                                    <h2>Medication Timetable</h2>

                                    <form method="get" class="modern-date-selector">
                                        <label for="date">Select Date</label>
                                        <div class="input-group">
                                            <input type="date" id="date" name="date" value="${selectedDate}">
                                            <button type="submit" class="btn-icon">&#128269;</button>
                                        </div>
                                    </form>

                                    <div class="timetable-results">
                                        <p class="results-header">Schedule for <strong>${selectedDate}</strong></p>

                                        <c:choose>
                                            <c:when test="${empty medications}">
                                                <div class="empty-mini-state">
                                                    <p>No schedule found.</p>
                                                </div>
                                            </c:when>
                                            <c:otherwise>
                                                <div class="timetable-scroll">
                                                    <c:forEach var="m" items="${medications}">
                                                        <div class="timetable-row">
                                                            <div class="row-info">
                                                                <h4>${m.medicineName}</h4>
                                                                <span>${m.dosage} • ${m.frequency}</span>
                                                            </div>
                                                            <span
                                                                class="status-pill status-${m.status == 'PENDING' ? 'pending' : m.status == 'TAKEN' ? 'taken' : 'missed'}">
                                                                ${m.status}
                                                            </span>
                                                        </div>
                                                    </c:forEach>
                                                </div>
                                            </c:otherwise>
                                        </c:choose>
                                    </div>
                                </div>

                                <!-- Notifications Widget (New) -->
                                <div class="card-panel">
                                    <h2>
                                        Recent Alerts
                                    </h2>

                                    <c:choose>
                                        <c:when test="${not empty notifications}">
                                            <ul class="notification-list-widget">
                                                <c:forEach var="n" items="${notifications}">
                                                    <li class="notification-item">
                                                        <div class="notif-header">
                                                            <span>&#128276; Alert</span>
                                                            <span>${n.formattedDate}</span>
                                                        </div>
                                                        <p class="notif-msg">${n.message}</p>
                                                    </li>
                                                </c:forEach>
                                            </ul>
                                        </c:when>
                                        <c:otherwise>
                                            <div class="empty-mini-state">
                                                <p>No new notifications</p>
                                            </div>
                                        </c:otherwise>
                                    </c:choose>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <jsp:include page="/WEB-INF/views/components/footer.jsp" />

        </body>

        </html>
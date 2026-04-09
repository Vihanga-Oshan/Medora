<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Guardian Profile | Medora</title>

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
                    color: white;
                    box-shadow: 0 4px 6px rgba(0, 120, 195, 0.2);
                }

                .btn-primary:hover {
                    background-color: var(--medical-blue-hover);
                    transform: translateY(-2px);
                }

                .dashboard-hero .btn-primary {
                    background-color: white;
                    color: #0078c3;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                }

                .dashboard-hero .btn-primary:hover {
                    background-color: #f8fbff;
                    transform: translateY(-2px);
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

                /* Profile Specific */
                .form-group {
                    margin-bottom: 15px;
                }

                .form-group label {
                    display: block;
                    margin-bottom: 5px;
                    color: var(--text-muted);
                    font-size: 0.9rem;
                }

                .form-group input {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: var(--radius-md);
                    font-size: 1rem;
                    color: var(--navy-dark);
                }

                .form-row {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 15px;
                }

                .patient-mini-card {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 10px;
                    border: 1px solid var(--border-color);
                    border-radius: var(--radius-md);
                    margin-bottom: 10px;
                }
            </style>
        </head>

        <body>

            <jsp:include page="/WEB-INF/views/components/header-guardian.jsp" />

            <div class="dashboard-wrapper">

                <!-- ===== Hero Section ===== -->
                <header class="dashboard-hero">
                    <div class="hero-content">
                        <h1 class="hero-title">My Profile</h1>
                        <p class="hero-subtitle">Manage your personal information and account settings.</p>
                    </div>
                </header>

                <div class="container main-layout">

                    <div class="content-grid">

                        <!-- Left: Profile Form -->
                        <div class="card-panel">
                            <h2>Personal Information</h2>

                            <c:if test="${not empty message}">
                                <div style="padding: 10px; margin-bottom: 20px; border-radius: var(--radius-md); 
                                     background: ${messageType == 'error' ? '#fee2e2' : '#dcfce7'}; 
                                     color: ${messageType == 'error' ? '#ef4444' : '#166534'};">
                                    ${message}
                                </div>
                            </c:if>

                            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                                <div
                                    style="width: 80px; height: 80px; background: #e0f2fe; color: #0078c3; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold;">
                                    ${guardian.name != null ? guardian.name.substring(0, 2).toUpperCase() : 'GU'}
                                </div>
                                <div>
                                    <h3 style="margin: 0;">${guardian.name}</h3>
                                    <p style="margin: 5px 0; color: var(--text-muted);">Guardian</p>
                                </div>
                            </div>

                            <form action="${pageContext.request.contextPath}/guardian/profile" method="post">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" value="${guardian.name}" required />
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" value="${guardian.email}" required />
                                </div>

                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="tel" name="contact" value="${guardian.contactNumber}" required />
                                </div>

                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>

                        <!-- Right: Settings & Linked Patients -->
                        <div>
                            <div class="card-panel">
                                <h2>Linked Patients</h2>
                                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">You are
                                    monitoring the following patients.</p>

                                <c:choose>
                                    <c:when test="${not empty patients}">
                                        <c:forEach var="p" items="${patients}">
                                            <div class="patient-mini-card">
                                                <div
                                                    style="width: 32px; height: 32px; background: #e0f2fe; color: #0078c3; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;">
                                                    ${p.name.substring(0, 2).toUpperCase()}
                                                </div>
                                                <div style="flex-grow: 1;"><strong>${p.name}</strong></div>
                                                <a href="${pageContext.request.contextPath}/guardian/patients?nic=${p.nic}"
                                                    style="color: var(--medical-blue); font-size: 0.85rem;">View</a>
                                            </div>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <p style="color: var(--text-muted);">No patients linked.</p>
                                    </c:otherwise>
                                </c:choose>

                                <!-- 
                                <button class="btn"
                                    style="width: 100%; border: 1px dashed var(--border-color); background: white; margin-top: 10px;">
                                    &#43; Link New Patient
                                </button>
                                -->
                            </div>

                            <div class="card-panel" style="margin-top: 20px;">
                                <h2>Security</h2>
                                <button class="btn"
                                    style="width: 100%; background: white; border: 1px solid var(--border-color); text-align: left; justify-content: space-between; display: flex;">
                                    <span>Change Password</span>
                                    <span>&rarr;</span>
                                </button>
                                <button class="btn"
                                    style="width: 100%; background: white; border: 1px solid var(--border-color); text-align: left; justify-content: space-between; display: flex; margin-top: 10px; color: var(--danger-red); border-color: #fecaca;">
                                    <span>Delete Account</span>
                                    <span>&#9888;</span>
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <jsp:include page="/WEB-INF/views/components/footer.jsp" />

        </body>

        </html>
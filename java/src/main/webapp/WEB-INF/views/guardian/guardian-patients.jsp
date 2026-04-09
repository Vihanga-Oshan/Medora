<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Patient Monitoring | Medora</title>

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

                /* Patient Selection Grid */
                .patient-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }

                .patient-selector {
                    background: var(--bg-card);
                    padding: 20px;
                    border-radius: var(--radius-md);
                    border: 1px solid var(--border-color);
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .patient-selector:hover {
                    transform: translateY(-3px);
                    box-shadow: var(--shadow-md);
                }

                .patient-selector.active {
                    border-color: var(--medical-blue);
                    background-color: #f0f9ff;
                }

                .avatar-circle {
                    width: 48px;
                    height: 48px;
                    background: #e2e8f0;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    color: var(--navy-dark);
                    font-size: 1.1rem;
                }

                /* Content Grid */
                .content-grid {
                    display: grid;
                    grid-template-columns: 1fr 2fr;
                    gap: 30px;
                }

                @media (max-width: 900px) {
                    .content-grid {
                        grid-template-columns: 1fr;
                    }
                }

                /* Card Panel */
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

                .info-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #f1f5f9;
                }

                .info-row:last-child {
                    border-bottom: none;
                }

                .info-label {
                    color: var(--text-muted);
                    font-size: 0.9rem;
                }

                .info-value {
                    font-weight: 500;
                    color: var(--navy-dark);
                }

                /* Medication Items */
                .med-item {
                    background: #f8fafc;
                    border-radius: var(--radius-md);
                    padding: 15px;
                    margin-bottom: 10px;
                    border-left: 4px solid var(--medical-blue);
                }

                .med-header {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 5px;
                }

                .med-name {
                    font-weight: 600;
                    font-size: 1rem;
                }

                .med-time {
                    font-size: 0.85rem;
                    color: var(--text-muted);
                }

                .med-status {
                    padding: 4px 8px;
                    border-radius: 12px;
                    font-size: 0.75rem;
                    font-weight: 700;
                    text-transform: uppercase;
                }

                .status-taken {
                    background: #dcfce7;
                    color: #166534;
                }

                .status-missed {
                    background: #fee2e2;
                    color: #991b1b;
                }

                .status-pending {
                    background: #fef3c7;
                    color: #92400e;
                }

                .patient-list-item:hover {
                    transform: translateX(5px);
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    border-color: var(--medical-blue);
                }

                .patient-list-item.active {
                    background-color: #f0f9ff !important;
                    border-color: var(--medical-blue) !important;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
                        <h1 class="hero-title">Patient Monitoring</h1>
                        <p class="hero-subtitle">Real-time medication tracking and adherence monitoring.</p>
                    </div>
                    <div class="hero-actions">
                        <button class="btn btn-primary"
                            style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px);"
                            onclick="document.getElementById('addPatientModal').style.display='block'">
                            &#43; Add Patient
                        </button>
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

                    <div class="patient-list-container"
                        style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
                        <c:choose>
                            <c:when test="${not empty patients}">
                                <c:forEach var="p" items="${patients}">
                                    <div class="patient-list-item ${p.nic == selectedPatient.nic ? 'active' : ''}"
                                        onclick="window.location.href='${pageContext.request.contextPath}/guardian/patients?nic=${p.nic}'"
                                        style="display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s;">

                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div class="avatar-circle"
                                                style="${p.nic == selectedPatient.nic ? 'background: #e0f2fe; color: #0078c3;' : ''}">
                                                ${p.name.substring(0, 2).toUpperCase()}
                                            </div>
                                            <div>
                                                <strong style="font-size: 1.05rem; color: #0f172a;">${p.name}</strong>
                                                <div style="font-size: 0.85rem; color: #64748b;">${p.gender}</div>
                                            </div>
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <form action="${pageContext.request.contextPath}/guardian/remove-patient"
                                                method="post" style="margin: 0;"
                                                onsubmit="return confirm('Are you sure you want to remove this patient?');"
                                                onclick="event.stopPropagation();">
                                                <input type="hidden" name="patientNic" value="${p.nic}">
                                                <button type="submit"
                                                    style="background: #fee2e2; border: 1px solid #fecaca; cursor: pointer; color: #b91c1c; font-size: 0.85rem; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                                    Remove
                                                </button>
                                            </form>
                                            <div style="color: #cbd5e1;">
                                                &#10095; <!-- Right chevron -->
                                            </div>
                                        </div>
                                    </div>
                                </c:forEach>
                            </c:when>
                            <c:otherwise>
                                <div class="card-panel"
                                    style="width: 100%; text-align: center; color: var(--text-muted); padding: 40px; border-radius: var(--radius-md);">
                                    <p style="font-size: 1.1rem; margin-bottom: 5px;">No patients linked to your account
                                        yet.</p>
                                    <p style="font-size: 0.9rem;">Please contact support to link a patient.</p>
                                </div>
                            </c:otherwise>
                        </c:choose>
                    </div>

                    <!-- Main Content -->
                    <div class="content-grid">

                        <!-- Left: Patient Info -->
                        <div class="card-panel">
                            <h2>Patient Profile</h2>
                            <c:if test="${not empty selectedPatient}">
                                <div style="text-align: center; margin-bottom: 20px;">
                                    <div class="avatar-circle"
                                        style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 10px; background: #e0f2fe; color: #0078c3;">
                                        ${selectedPatient.name.substring(0, 2).toUpperCase()}</div>
                                    <h3 style="margin: 0;">${selectedPatient.name}</h3>
                                    <p style="color: var(--text-muted); margin: 5px 0;">${selectedPatient.gender}</p>
                                </div>

                                <div class="info-row">
                                    <span class="info-label">Chronic Issues</span>
                                    <span class="info-value"
                                        style="text-align: right;">${selectedPatient.chronicIssues}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Allergies</span>
                                    <span class="info-value"
                                        style="text-align: right;">${selectedPatient.allergies}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Contact</span>
                                    <span class="info-value" style="text-align: right;">${selectedPatient.phone}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Emergency</span>
                                    <span class="info-value"
                                        style="text-align: right;">${selectedPatient.emergencyContact}</span>
                                </div>
                            </c:if>
                            <c:if test="${empty selectedPatient}">
                                <p>Select a patient to view details.</p>
                            </c:if>
                        </div>

                        <!-- Right: Schedule -->
                        <div class="card-panel">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                                <h2 style="border: none; margin: 0; padding: 0;">Today's Schedule</h2>
                                <div style="display: flex; gap: 10px;">
                                    <button class="btn"
                                        style="padding: 6px 12px; font-size: 0.85rem; background: var(--medical-blue); color: white;">All</button>
                                    <button class="btn"
                                        style="padding: 6px 12px; font-size: 0.85rem; background: #f1f5f9;">Missed</button>
                                </div>
                            </div>

                            <div class="schedule-list">
                                <c:choose>
                                    <c:when test="${not empty medications}">
                                        <c:forEach var="med" items="${medications}">
                                            <div class="med-item"
                                                style="border-left-color: ${med.status == 'TAKEN' ? 'var(--success-green)' : (med.status == 'MISSED' ? 'var(--danger-red)' : 'var(--warning-yellow)')};">
                                                <div class="med-header">
                                                    <span class="med-name">${med.medicineName}</span>
                                                    <span
                                                        class="med-status ${med.status == 'TAKEN' ? 'status-taken' : (med.status == 'MISSED' ? 'status-missed' : 'status-pending')}">
                                                        ${med.status}
                                                    </span>
                                                </div>
                                                <div class="med-time">${med.dosage} • ${med.frequency} •
                                                    ${med.mealTiming}</div>
                                            </div>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <p style="padding: 15px; color: var(--text-muted);">No medications scheduled for
                                            today.</p>
                                    </c:otherwise>
                                </c:choose>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Add Patient Modal -->
            <div id="addPatientModal"
                style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
                <div
                    style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 400px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <span style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;"
                        onclick="document.getElementById('addPatientModal').style.display='none'">&times;</span>
                    <h3 style="margin-top: 0; color: #0f172a;">Link New Patient</h3>
                    <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 20px;">Enter the NIC of the patient.
                        They must have already listed you as their guardian.</p>

                    <form action="${pageContext.request.contextPath}/guardian/add-patient" method="post">
                        <div style="margin-bottom: 15px;">
                            <label for="patientNic"
                                style="display: block; margin-bottom: 5px; font-weight: 500;">Patient NIC</label>
                            <input type="text" id="patientNic" name="patientNic" required
                                style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 5px;">
                        </div>
                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button"
                                onclick="document.getElementById('addPatientModal').style.display='none'"
                                style="padding: 10px 15px; border: 1px solid #e2e8f0; background: white; border-radius: 5px; cursor: pointer;">Cancel</button>
                            <button type="submit" class="btn-primary"
                                style="padding: 10px 15px; border: none; cursor: pointer;">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>

            <jsp:include page="/WEB-INF/views/components/footer.jsp" />

        </body>

        </html>
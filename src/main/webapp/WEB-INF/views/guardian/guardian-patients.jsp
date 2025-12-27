<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Patient Monitoring | Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/main.css"/>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/patients.css"/>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css"/>
</head>
<body>

<jsp:include page="/WEB-INF/views/components/header-guardian.jsp"/>

<main class="container">
    <h1 class="section-title">Patient Monitoring</h1>
    <p class="section-subtitle">Real-time medication tracking and adherence monitoring</p>

    <!-- Patient Selector -->
    <div class="card">
        <h3>Select Patient</h3>
        <div class="flex mt-2">
            <div class="patient-selector active" style="background: var(--primary-blue); color: white;">
                <strong>Eleanor Rodriguez</strong><br/>
                <span class="text-sm">92% adherence</span>
            </div>
            <div class="patient-selector">
                <div style="display: flex; justify-content: center;">
                    <div class="avatar-circle">RC</div>
                </div>
                <strong>Robert Chen</strong><br/>
                <span class="text-sm">78% adherence</span>
            </div>
            <div class="patient-selector">
                <div style="display: flex; justify-content: center;">
                    <div class="avatar-circle">MW</div>
                </div>
                <strong>Margaret Wilson</strong><br/>
                <span class="text-sm">95% adherence</span>
            </div>
        </div>
    </div>

    <div class="flex justify-between">
        <!-- Patient Info Card -->
        <div class="card" style="width: 30%;">
            <div class="flex align-center mb-2">
                <div class="avatar-circle large">ER</div>
                <div>
                    <strong>Eleanor Rodriguez</strong><br/>
                    <span class="text-sm">Age 72</span>
                </div>
            </div>

            <div class="mt-2">
                <strong>Adherence Rate</strong>
                <div class="flex align-center justify-between">
                    <span style="font-size: 1.5rem; font-weight: bold;">92%</span>
                    <span class="badge">Excellent</span>
                </div>
                <div class="progress-bar">
                    <div style="width: 92%; background: #4caf50;"></div>
                </div>
            </div>

            <div class="mt-2">
                <strong>Medical Conditions</strong><br/>
                Hypertension, Type 2 Diabetes
            </div>

            <div class="mt-2">
                <strong>Last Updated</strong><br/>
                <span class="text-sm">📅 2 hours ago</span>
            </div>

            <div class="mt-2">
                <strong>Quick Stats</strong>
                <div class="flex mt-2">
                    <div class="quick-stat taken">
                        <strong>3</strong><br/>
                        <span class="text-sm">Taken</span>
                    </div>
                    <div class="quick-stat missed">
                        <strong>0</strong><br/>
                        <span class="text-sm">Missed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medication Schedule -->
        <div class="card" style="width: 68%;">
            <h2>Today's Medication Schedule</h2>
            <p class="section-subtitle">Current medication timetable and status</p>

            <div class="filter-buttons">
                <button class="btn active">All</button>
                <button class="btn">Taken</button>
                <button class="btn">Upcoming</button>
                <button class="btn">Missed</button>
            </div>

            <!-- Medication Items -->
            <c:forEach var="med" items="${medications}">
                <div class="med-item">
                    <div class="flex justify-between align-center">
                        <div>
                            <strong>${med.name}</strong><br/>
                            <span class="text-sm">${med.dosage}</span><br/>
                            <span class="text-xs">⏰ ${med.time}</span>
                        </div>
                        <div class="flex align-center">
                            <span class="badge ${med.statusClass}">${med.statusLabel}</span>
                        </div>
                    </div>
                    <div class="mt-1 med-note">
                        <span>ℹ️ ${med.instructions}</span>
                    </div>
                </div>
            </c:forEach>
        </div>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2025 Medora. All rights reserved.</p>
</footer>

<jsp:include page="/WEB-INF/views/components/footer.jsp" />

</body>
</html>

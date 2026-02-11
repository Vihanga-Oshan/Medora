<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Medora - Dashboard</title>
            <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
        </head>

        <body>
            <div class="container">
                <!-- Include Sidebar Component -->
                <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

                    <!-- Main Content -->
                    <main class="main-content">
                        <!-- Header -->
                        <header class="header">
                            <div class="user-info">
                                <img src="${pageContext.request.contextPath}/assets/register-patient1.png"
                                    alt="User Avatar" class="avatar">
                                <span class="user-role">${not empty pharmacistName ? pharmacistName :
                                    'Pharmacist'}</span>
                            </div>
                            <div class="greeting">
                                <span class="greeting-icon">☀️</span>
                                <div>
                                    <span class="greeting-text">${not empty greeting ? greeting : 'Good Day'}</span>
                                    <span class="date-time">${currentDate} • ${currentTime}</span>
                                </div>
                            </div>
                        </header>

                        <!-- Page Title -->
                        <h2 class="page-title">Dashboard</h2>

                        <!-- Metric Cards -->
                        <div class="metric-cards">
                            <div class="metric-card">
                                <div class="metric-value">${pendingCount != null ? pendingCount : 0}</div>
                                <div class="metric-label">Pending Prescriptions</div>
                                <a href="${pageContext.request.contextPath}/pharmacist/validate" class="see-all">See
                                    All</a>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value">${approvedCount != null ? approvedCount : 0}</div>
                                <div class="metric-label">Pending Schedules</div>
                                <a href="${pageContext.request.contextPath}/pharmacist/approved-prescriptions"
                                    class="see-all">See All</a>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value">${newPatientCount != null ? newPatientCount : 0}</div>
                                <div class="metric-label">New patients (last 24 hrs)</div>
                                <a href="${pageContext.request.contextPath}/pharmacist/patients" class="see-all">See
                                    All</a>
                            </div>
                        </div>

                        <!-- Patient Table 1: Check Actions -->
                        <div class="table-container">
                            <div class="table-header">
                                <h3>Patient List — Check Required</h3>
                                <a href="${pageContext.request.contextPath}/pharmacist/validate"
                                    class="see-all-link">See All »</a>
                            </div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Chronic Condition</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <c:choose>
                                        <c:when test="${not empty patientsNeedingCheck}">
                                            <c:forEach var="patient" items="${patientsNeedingCheck}">
                                                <tr>
                                                    <td>${patient.name}</td>
                                                    <td>${patient.condition}</td>
                                                    <td><a href="${pageContext.request.contextPath}/pharmacist/validate"
                                                            class="action-link">Check</a></td>
                                                </tr>
                                            </c:forEach>
                                        </c:when>
                                        <c:otherwise>
                                            <tr>
                                                <td colspan="3" style="text-align: center; color:#888;">No patients
                                                    requiring checks at this time.</td>
                                            </tr>
                                        </c:otherwise>
                                    </c:choose>
                                </tbody>
                            </table>
                        </div>

                        <!-- Patient Table 2: Schedule Actions -->
                        <div class="table-container">
                            <div class="table-header">
                                <h3>Patient List — Schedule Required</h3>
                                <a href="${pageContext.request.contextPath}/pharmacist/approved-prescriptions"
                                    class="see-all-link">See All »</a>
                            </div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Chronic Condition</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <c:choose>
                                        <c:when test="${not empty patientsNeedingSchedule}">
                                            <c:forEach var="patient" items="${patientsNeedingSchedule}">
                                                <tr>
                                                    <td>${patient.name}</td>
                                                    <td>${patient.condition}</td>
                                                    <td><a href="${pageContext.request.contextPath}/pharmacist/approved-prescriptions"
                                                            class="action-link">Schedule</a></td>
                                                </tr>
                                            </c:forEach>
                                        </c:when>
                                        <c:otherwise>
                                            <tr>
                                                <td colspan="3" style="text-align: center; color:#888;">No patients
                                                    requiring schedules at this time.</td>
                                            </tr>
                                        </c:otherwise>
                                    </c:choose>
                                </tbody>
                            </table>
                        </div>
                    </main>
            </div>
        </body>

        </html>
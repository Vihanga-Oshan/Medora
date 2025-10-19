<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ page contentType="text/html;charset=UTF-8" %>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/dashboard.css">
</head>
<body>

<!-- Include top navigation -->
<%@ include file="/WEB-INF/views/components/header.jsp" %>

<div class="container">

    <!-- ===== Welcome Section ===== -->
    <h2>Hello ${patient.name}!</h2>
    <p>Let’s stay on track with your prescriptions</p>

    <!-- ===== Stats Section ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <span>Total Doses</span>
            <div class="stat-value">${totalCount}</div>
        </div>
        <div class="stat-card stat-taken">
            <span>Taken</span>
            <div class="stat-value">${takenCount}</div>
        </div>
        <div class="stat-card stat-pending">
            <span>Pending</span>
            <div class="stat-value">${pendingCount}</div>
        </div>
        <div class="stat-card stat-missed">
            <span>Missed</span>
            <div class="stat-value">${missedCount}</div>
        </div>
    </div>

    <!-- ===== Today's Medication Section ===== -->
    <div class="card">
        <h2 class="card-title">Today's Medication Schedule</h2>

        <p class="card-subtitle">
            <%= java.time.LocalDate.now().getDayOfWeek().toString().substring(0,1).toUpperCase() +
                    java.time.LocalDate.now().getDayOfWeek().toString().substring(1).toLowerCase() %>,
            <%= java.time.LocalDate.now().getMonth().toString().substring(0,1).toUpperCase() +
                    java.time.LocalDate.now().getMonth().toString().substring(1).toLowerCase() %>
            <%= java.time.LocalDate.now().getDayOfMonth() %>,
            <%= java.time.LocalDate.now().getYear() %>
        </p>

        <c:choose>
            <c:when test="${empty medications}">
                <p style="text-align:center; color:#6c757d;">No medications scheduled for today.</p>
            </c:when>
            <c:otherwise>
                <c:forEach var="m" items="${medications}">
                    <div class="med-card">
                        <div class="med-info">
                            <h3>${m.medicineName}</h3>
                            <div class="med-details">
                                <span>${m.dosage} : ${m.frequency}</span>
                                <em>${m.mealTiming}</em>
                                <span>${m.instructions}</span>
                            </div>
                        </div>

                        <div class="med-actions">
                            <form action="${pageContext.request.contextPath}/patient/mark-medication" method="post">
                                <input type="hidden" name="scheduleId" value="${m.id}" />
                                <input type="hidden" name="patientNic" value="${sessionScope.patient.nic}" />
                                <input type="hidden" name="status" value="TAKEN" />
                                <button type="submit" class="btn-taken">Mark as Taken</button>
                            </form>

                            <form action="${pageContext.request.contextPath}/patient/mark-medication" method="post">
                                <input type="hidden" name="scheduleId" value="${m.id}" />
                                <input type="hidden" name="patientNic" value="${sessionScope.patient.nic}" />
                                <input type="hidden" name="status" value="MISSED" />
                                <button type="submit" class="btn-missed">Mark as Missed</button>
                            </form>
                        </div>
                    </div>
                </c:forEach>

            </c:otherwise>
        </c:choose>
    </div>

    <div class="full-timetable">
        <h2>View Full Medication Timetable</h2>

        <form method="get" class="date-selector">
            <label for="date">Select Date:</label>
            <input type="date" id="date" name="date" value="${selectedDate}">
            <button type="submit" class="btn-submit">View</button>
        </form>

        <p><strong>Schedule for ${selectedDate}</strong></p>

        <c:choose>
            <c:when test="${empty medications}">
                <p style="text-align:center; color:#6b7280;">No medications scheduled for this date.</p>
            </c:when>
            <c:otherwise>
                <c:forEach var="m" items="${medications}">
                    <div class="timetable-card">
                        <div class="timetable-info">
                            <h3>${m.medicineName}</h3>
                            <p>${m.dosage} : ${m.frequency}</p>
                            <em>${m.mealTiming}</em>
                            <p>${m.instructions}</p>
                        </div>
                        <div>
                        <span class="status-badge
                            ${m.status == 'PENDING' ? 'status-pending' :
                              m.status == 'TAKEN' ? 'status-taken' : 'status-missed'}">
                                ${m.status}
                        </span>
                        </div>
                    </div>
                </c:forEach>
            </c:otherwise>
        </c:choose>
    </div>

</div>

</body>
</html>

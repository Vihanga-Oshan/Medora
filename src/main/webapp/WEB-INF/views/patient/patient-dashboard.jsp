<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Patient Dashboard</title>

    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/dashboard.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp"/>

<main class="container">
    <h1>Welcome back, chenal!</h1>
    <p>Here's your medication schedule for today</p>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <span>Total Doses</span>
            <div class="stat-value">3</div>
        </div>
        <div class="stat-card stat-taken">
            <span>Taken</span>
            <div class="stat-value">1</div>
        </div>
        <div class="stat-card stat-pending">
            <span>Pending</span>
            <div class="stat-value">1</div>
        </div>
        <div class="stat-card stat-missed">
            <span>Missed</span>
            <div class="stat-value">1</div>
        </div>
    </div>

    <!-- Medication List -->
    <div class="card">
        <h2>Today's Medication Schedule</h2>
        <p>Monday, October 13, 2025</p>

        <!-- Medication 1 (Taken) -->
        <div class="medication-item taken">
            <div class="med-icon green">←</div>
            <div class="med-info">
                <strong>Metformin</strong>
                <div class="time">500mg • 08:00 AM</div>
            </div>
            <div class="action-buttons">
                <button class="btn btn-success">✔ Taken</button>
            </div>
        </div>

        <!-- Medication 2 (Pending) -->
        <div class="medication-item pending">
            <div class="med-icon blue">⏳</div>
            <div class="med-info">
                <strong>Lisinopril</strong>
                <div class="time">10mg • 12:00 PM</div>
            </div>
            <div class="action-buttons">
                <button class="btn btn-success">✔ Taken</button>
                <button class="btn btn-danger">✘ Missed</button>
            </div>
        </div>

    </div>
</main>

</body>
</html>

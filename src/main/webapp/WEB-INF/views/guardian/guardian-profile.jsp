<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Guardian Profile | Medora</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css"/>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/profile.css"/>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/guardian/alerts.css" />
</head>
<body>

<jsp:include page="/WEB-INF/views/components/header-guardian.jsp"/>
<div class="profile-container">
    <h2>Profile</h2>
    <p class="description">View and manage your linked patients and account settings</p>

    <!-- Linked Patients Section -->
    <div class="section-box">
        <h3>Linked Patients</h3>
        <div class="patients-grid">
            <div class="patient-card">
                <strong>Eleanor Rodriguez</strong>
                <span class="badge">Patient</span>
                <p>Adherence: 92%</p>
            </div>
            <div class="patient-card">
                <strong>Robert Chen</strong>
                <span class="badge">Patient</span>
                <p>Adherence: 78%</p>
            </div>
        </div>
    </div>

    <!-- Account Info Section -->
    <div class="section-box account-info">
        <h3>Account Details</h3>
        <p><label>Full Name</label><br>John Doe</p>
        <p><label>Email</label><br>guardian@example.com</p>
        <p><label>Phone</label><br>+94 77 123 4567</p>
    </div>

    <!-- Change Password Section -->
    <div class="section-box change-password">
        <h3>Change Password</h3>
        <form method="post" action="${pageContext.request.contextPath}/guardian/change-password">
            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password">

            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password">

            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">

            <button type="submit">Update Password</button>
        </form>
    </div>


</div>


<footer class="footer">
    <p>&copy; 2025 Medora. All rights reserved.</p>
</footer>

</body>
</html>

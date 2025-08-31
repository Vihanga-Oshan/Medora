<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>

<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Medora</title>

    <!-- Use context path; avoids ../../ breakage -->
    <link rel="stylesheet" href="${cp}/css/login/login-patient.css" />
</head>
<body>

<header class="navbar">
    <div class="logo-brand">
        <img src="${cp}/assets/logo.png" alt="Medora Logo" class="logo" />
        <span class="small-brand">Medora</span>
    </div>
</header>

<div class="container">
    <div class="left-panel">
        <img src="${cp}/assets/login-patient.jpg" alt="login image" class="register-image" />
    </div>

    <div class="right-panel">
        <div class="form-card">
            <div class="form-header">
                <img src="${cp}/assets/welcome.png" alt="welcome icon" class="welcome-icon" />
                <h2>Welcome back</h2>

                <div class="toggle-buttons">
                    <button class="active" onclick="location.href='${cp}/login'">Login as Patient</button>
                    <button class="not-active" onclick="location.href='${cp}/loginguardian'">Login as Guardian</button>

                </div>
            </div>


            <form method="post" action="${cp}/login" novalidate>
                <input type="text" name="nic" placeholder="NIC" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" class="submit-btn">Login</button>

                <div class="forgot-password">
                    <a href="#">Forgot your password?</a>
                </div>


                <c:if test="${not empty error}">
                    <p class="error-text" style="color:#e11d48;margin-top:.75rem;">${error}</p>
                </c:if>

            </form>
        </div>
    </div>
</div>

</body>
</html>

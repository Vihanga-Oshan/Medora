<%@ page contentType="text/html; charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Medora</title>
    <link rel="stylesheet" href="${cp}/css/login/login-patient.css" />
</head>
<body class="login-page">

<div class="login-container">

    <!-- Back to Home -->
    <div class="back-link" onclick="window.location.href='${cp}/index.jsp'">← Back to Home</div>

    <!-- Logo -->
    <div class="logo">
        <img src="${cp}/assets/logo.png" alt="Medora Logo" />
    </div>

    <!-- Heading -->
    <h1>Welcome back</h1>
    <p class="subtitle">Log in to continue your Medora journey</p>

    <!-- Toggle Buttons -->
    <div class="form-toggle">
        <button class="active" type="button" onclick="location.href='${cp}/login'">Patient</button>
        <button type="button" onclick="location.href='${cp}/guardian/login'">Guardian</button>
    </div>

    <!-- Login Form -->
    <form method="post" action="${cp}/login" novalidate
          onsubmit="return window.loginValidate && window.loginValidate(this);">

        <div class="form">
            <label for="nic">NIC</label>
            <input type="text" name="nic" id="nic" value="${param.nic}" placeholder="Enter your NIC" required />

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required />

            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember" /> Remember me
                </label>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">Login</button>

            <!-- Display backend error -->
            <c:if test="${not empty error}">
                <p class="error-text" style="color:#e11d48;margin-top:.75rem;">${error}</p>
            </c:if>

            <!-- Optional success message (if set by backend) -->
            <c:if test="${not empty message}">
                <p class="success-text" style="color:#16a34a;margin-top:.75rem;">${message}</p>
            </c:if>

            <p class="bottom-text">Don’t have an account?
                <a href="${cp}/patient/register">Create one here</a>
            </p>
        </div>
    </form>
</div>

<script src="${cp}/js/form-validation.js?v=1" defer></script>
</body>
</html>

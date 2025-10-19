<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Account - Guardian | Medora</title>
    <link rel="stylesheet" href="${cp}/css/register/register-guardian.css" />

    <!-- Optional inline error fallback -->
    <style>
        .invalid { outline: 2px solid #e11d48; }
        .error-text { color: #e11d48; margin-top: .5rem; font-size: .9rem; }
    </style>
</head>
<body>

<div class="register-box">

    <!-- Back to Home -->
    <div class="back-link" onclick="window.location.href='${cp}/index.jsp'">← Back to Home</div>

    <!-- Logo -->
    <div class="logo">
        <img src="${cp}/assets/logo.png" alt="Medora Logo" />
    </div>

    <!-- Title -->
    <h1>Create Account</h1>
    <p class="subtitle">Manage your patients’ health with care</p>

    <!-- Toggle Buttons -->
    <div class="toggle-btns">
        <button type="button" onclick="location.href='${cp}/register/patient'">Patient</button>
        <button type="button" class="active">Guardian</button>
    </div>

    <!-- Server Error -->
    <c:if test="${not empty error}">
        <p class="error-text">${error}</p>
    </c:if>

    <!-- Guardian Registration Form -->
    <form method="post" action="${cp}/register/guardian"
          id="guardianForm"
          novalidate
          onsubmit="return window.guardianValidate && window.guardianValidate(this);">

        <div class="form-grid">
            <div class="input-group full">
                <label for="g_name">Full Name</label>
                <input type="text" id="g_name" name="g_name" required value="${param.g_name}" />
            </div>

            <div class="input-group full">
                <label for="nic">NIC</label>
                <input type="text" id="nic" name="nic" inputmode="numeric" required value="${param.nic}" />
            </div>

            <div class="input-group full">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number"
                       inputmode="tel" required value="${param.contact_number}" />
            </div>

            <div class="input-group full">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" required value="${param.email}" />
            </div>

            <div class="input-group full">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required />
            </div>

            <div class="checkbox full">
                <label>
                    <input type="checkbox" id="agree" name="agree" required />
                    I agree to the Privacy Policies
                </label>
            </div>

            <div class="input-group full">
                <button type="submit" class="btn-submit">Create Account</button>
            </div>
        </div>
    </form>

    <p class="login-text">Already have an account? <a href="${cp}/loginguardian">Login here</a></p>

</div>
<script src="${cp}/js/form-validation.js?v=1" defer></script>
</body>
</html>

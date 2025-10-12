<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="cp" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Medora - Guardian Registration</title>
    <link rel="stylesheet" href="${cp}/css/register/register-guardian.css" />
</head>
<body>

<header class="navbar">
    <div class="logo-brand">
        <img src="${cp}/assets/logo.png" alt="Medora Logo" class="logo" />
        <h1 class="brand-name">Medora</h1>
    </div>
</header>

<main class="container">
    <div class="left-side">
        <img src="${cp}/assets/reg-guardian.webp" class="guardian-logo" alt="guardian"/>
        <div class="image-placeholder"></div>
    </div>

    <div class="right-side">
        <!-- Global error (from servlet) -->
        <c:if test="${not empty error}">
            <p style="color:#e11d48;margin:.5rem 0">${error}</p>
        </c:if>

        <form
                id="guardianForm"
                class="register-form"
                method="post"
                action="${cp}/register/guardian"
                novalidate
                onsubmit="return window.guardianValidate && window.guardianValidate(this);"
        >
            <img src="${cp}/assets/c-a-icon.png" alt="create account icon" class="create-acc-icon" />
            <h2>Create your account</h2>

            <div class="toggle-buttons">
                <button type="button" class="toggle" onclick="location.href='${cp}/register/patient'">Register as Patient</button>
                <button type="button" class="toggle active">Register as Guardian</button>
            </div>

            <div class="field">
                <input type="text" name="g_name" placeholder="User name" autocomplete="name"
                       required value="${param.g_name}" />
            </div>

            <div class="field">
                <input type="text" name="nic" placeholder="NIC" inputmode="numeric" autocomplete="off"
                       required value="${param.nic}" />
            </div>

            <div class="field">
                <input type="text" name="contact_number" placeholder="Contact Number" inputmode="tel" autocomplete="tel"
                       required value="${param.contact_number}" />
            </div>

            <div class="field">
                <input type="email" name="email" placeholder="Email" autocomplete="email"
                       value="${param.email}" />
            </div>

            <div class="field">
                <input type="password" name="password" placeholder="Password" autocomplete="new-password"
                       required />
            </div>

            <div class="checkbox field">
                <label>
                    <input type="checkbox" id="agree" name="agree" required />
                    I agree to the Privacy Policies
                </label>
            </div>

            <button type="submit" class="register-button">Register</button>
        </form>

    </div>
</main>

<script src="${cp}/js/form-validation.js?v=1" defer></script>



</body>
</html>

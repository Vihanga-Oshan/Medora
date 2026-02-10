<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <c:set var="cp" value="${pageContext.request.contextPath}" />

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Create Account - Guardian | Medora</title>
            <link rel="stylesheet" href="${cp}/css/common.css" />
            <link rel="stylesheet" href="${cp}/css/auth.css" />
        </head>

        <body class="auth-page">

            <div class="register-box">

                <!-- Back to Home -->
                <div class="back-link" onclick="window.location.href='${cp}/index.jsp'">← Back to Home</div>

                <!-- Logo -->
                <div class="logo">
                    <img src="${cp}/assets/logo.png" alt="Medora Logo" />
                </div>

                <!-- Title -->
                <h1>Create Account</h1>
                <p class="subtitle">Manage your patients' health with care</p>

                <!-- Toggle Buttons -->
                <div class="toggle-btns">
                    <button type="button" onclick="location.href='${cp}/patient/register'">Patient</button>
                    <button type="button" class="active">Guardian</button>
                </div>

                <!-- Server Error -->
                <c:if test="${not empty error}">
                    <p class="error-text">${error}</p>
                </c:if>

                <!-- Guardian Registration Form -->
                <form method="post" action="${cp}/guardian/register" id="guardianForm" novalidate
                    onsubmit="return window.guardianValidate && window.guardianValidate(this);">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="g_name">Full Name</label>
                            <input type="text" id="g_name" name="g_name" class="form-input" required
                                value="${param.g_name}" />
                        </div>

                        <div class="form-group">
                            <label for="nic">NIC</label>
                            <input type="text" id="nic" name="nic" class="form-input" inputmode="numeric" required
                                value="${param.nic}" />
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-input"
                                inputmode="tel" required value="${param.contact_number}" />
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-input" autocomplete="email" required
                                value="${param.email}" />
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-input"
                                autocomplete="new-password" required />
                        </div>

                        <div class="form-options">
                            <label>
                                <input type="checkbox" id="agree" name="agree" required />
                                I agree to the Privacy Policies
                            </label>
                        </div>

                        <button type="submit" class="btn-auth-submit">Create Account</button>
                    </div>
                </form>

                <p class="bottom-text">Already have an account? <a href="${cp}/guardian/login">Login here</a></p>
            </div>
            <script src="${cp}/js/form-validation.js?v=1" defer></script>
        </body>

        </html>
<%@ page contentType="text/html;charset=UTF-8" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>Pharmacist Login - Medora</title>
            <link rel="stylesheet" href="${pageContext.request.contextPath}/css/common.css" />
            <link rel="stylesheet" href="${pageContext.request.contextPath}/css/auth.css" />
        </head>

        <body class="auth-page">

            <div class="auth-container">

                <!-- Back to Home -->
                <div class="back-link" onclick="window.location.href='${pageContext.request.contextPath}/index.jsp'">←
                    Back to Home</div>

                <!-- Logo -->
                <div class="logo">
                    <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo" />
                </div>

                <!-- Heading -->
                <h1>Pharmacist Login</h1>
                <p class="subtitle">Enter your ID and password to continue</p>

                <!-- Feedback messages -->
                <c:if test="${not empty error}">
                    <p class="error-text">${error}</p>
                </c:if>
                <c:if test="${not empty message}">
                    <p class="success-text">${message}</p>
                </c:if>

                <!-- Login Form -->
                <form id="loginForm" action="${pageContext.request.contextPath}/pharmacist/login" method="post"
                    novalidate>
                    <div class="form-group">
                        <label for="pharmacistId">Pharmacist ID</label>
                        <input type="text" id="pharmacistId" name="pharmacistId" class="form-input"
                            placeholder="Enter your pharmacist ID" required />
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-input"
                            placeholder="Enter your password" required />
                    </div>

                    <button type="submit" id="submitBtn" class="btn-auth-submit">Sign in</button>
                </form>

                <p class="bottom-text">
                    <a href="${pageContext.request.contextPath}/pharmacist/register">Create a pharmacist account</a>
                </p>
            </div>

            <script>
                (function () {
                    const form = document.getElementById('loginForm');
                    const pwd = document.getElementById('password');
                    const toggle = document.getElementById('toggle');
                    const submitBtn = document.getElementById('submitBtn');

                    toggle.addEventListener('click', function () {
                        if (pwd.type === 'password') {
                            pwd.type = 'text';
                            toggle.textContent = 'Hide';
                            toggle.setAttribute('aria-label', 'Hide password');
                        } else {
                            pwd.type = 'password';
                            toggle.textContent = 'Show';
                            toggle.setAttribute('aria-label', 'Show password');
                        }
                    });

                    form.addEventListener('submit', function (e) {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            form.reportValidity();
                            return;
                        }
                        submitBtn.disabled = true;
                        const originalText = submitBtn.textContent;
                        submitBtn.textContent = 'Signing in...';
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }, 5000);
                    });
                })();
            </script>
        </body>

        </html>
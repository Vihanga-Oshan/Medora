<%@ page contentType="text/html;charset=UTF-8" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <c:set var="cp" value="${pageContext.request.contextPath}" />

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>Pharmacist Login - Medora</title>

            <!-- Design Tokens and Base Styles -->
            <link rel="stylesheet" href="${cp}/css/common.css" />
            <link rel="stylesheet" href="${cp}/css/auth.css" />

            <!-- Custom Fonts -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link
                href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
                rel="stylesheet">
        </head>

        <body class="auth-page">
            <!-- Decorative Blobs from common.css -->
            <div class="decorative-blob blob-1"></div>
            <div class="decorative-blob blob-2"></div>
            <div class="decorative-blob blob-3"></div>

            <div class="auth-container animate-in">
                <!-- Back to Home -->
                <a href="${cp}/index.jsp" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Home
                </a>

                <!-- Logo -->
                <div class="logo">
                    <img src="${cp}/assets/logo.png" alt="Medora Logo" />
                    <span class="brand-text">Medora For Pharmacists</span>
                </div>

                <!-- Heading -->
                <h1>Pharmacist Login</h1>
                <p class="subtitle">Access your professional dashboard</p>

                <!-- Login Form -->
                <form id="loginForm" action="${cp}/pharmacist/login" method="post" novalidate>
                    <div class="form-group">
                        <label for="pharmacistId">Pharmacist ID</label>
                        <div class="input-wrapper">
                            <input type="text" id="pharmacistId" name="pharmacistId" class="form-input"
                                placeholder="Enter your pharmacist ID" required />
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                    </path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Enter your password" required />
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <button type="button" id="togglePassword" class="password-toggle-btn"
                                style="position: absolute; right: 16px; background: none; border: none; color: var(--text-gray); cursor: pointer; display: flex; align-items: center;">
                                <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn-auth-submit">
                        Sign In
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>

                    <!-- Feedback messages -->
                    <c:if test="${not empty error}">
                        <div class="message-box error-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span>${error}</span>
                        </div>
                    </c:if>
                    <c:if test="${not empty message}">
                        <div class="message-box success-box">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span>${message}</span>
                        </div>
                    </c:if>

                    <p class="bottom-text">
                        Need an account?
                        <a href="${cp}/pharmacist/register">Join as Pharmacist</a>
                    </p>
                </form>
            </div>

            <!-- Background Elements -->
            <div class="mesh-background"></div>

            <script>
                (function () {
                    const form = document.getElementById('loginForm');
                    const pwd = document.getElementById('password');
                    const toggle = document.getElementById('togglePassword');
                    const eyeIcon = document.getElementById('eyeIcon');
                    const submitBtn = document.getElementById('submitBtn');

                    toggle.addEventListener('click', function () {
                        if (pwd.type === 'password') {
                            pwd.type = 'text';
                            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                        } else {
                            pwd.type = 'password';
                            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                        }
                    });

                    form.addEventListener('submit', function (e) {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            form.reportValidity();
                            return;
                        }
                        submitBtn.disabled = true;
                        const originalContent = submitBtn.innerHTML;
                        submitBtn.innerHTML = 'Signing in...';
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalContent;
                        }, 5000);
                    });
                })();
            </script>
        </body>

        </html>
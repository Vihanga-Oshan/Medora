<%@ page contentType="text/html; charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>

        <c:set var="cp" value="${pageContext.request.contextPath}" />

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Login - Medora</title>

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
                    <span class="brand-text">Medora</span>
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
                    <div class="form-group">
                        <label for="nic">NIC Number</label>
                        <div class="input-wrapper">
                            <input type="text" name="nic" id="nic" value="${param.nic}" class="form-input"
                                placeholder="e.g. 200123456789" required />
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" class="form-input"
                                placeholder="Enter your password" required />
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" />
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="${cp}/forgot-password" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-auth-submit">
                        Sign In
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>

                    <!-- Display Messages -->
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
                        Don’t have an account?
                        <a href="${cp}/patient/register">Create Account</a>
                    </p>
                </form>
            </div>

            <!-- Background Elements -->
            <div class="mesh-background"></div>

            <script src="${cp}/js/form-validation.js?v=1" defer></script>
        </body>

        </html>
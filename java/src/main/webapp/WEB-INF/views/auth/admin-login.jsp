<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <c:set var="cp" value="${pageContext.request.contextPath}" />

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Admin Login - Medora</title>

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
                    <span class="brand-text">Medora Administration</span>
                </div>

                <!-- Heading -->
                <h1>Admin Login</h1>
                <p class="subtitle">Enter your credentials to access the console</p>

                <!-- Login Form -->
                <form action="${cp}/admin/login" method="post" novalidate>
                    <div class="form-group">
                        <label for="email">Administrator Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" id="email" class="form-input"
                                placeholder="admin@medora.com" required />
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                    </path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Administrator Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" class="form-input"
                                placeholder="••••••••" required />
                            <span class="input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-submit">
                        Access Dashboard
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
                </form>

                <div class="bottom-text">
                    <p>Don't have an administrator account? <a href="${cp}/admin/register">Register</a></p>
                </div>
            </div>

            <!-- Background Elements -->
            <div class="mesh-background"></div>
        </body>

        </html>